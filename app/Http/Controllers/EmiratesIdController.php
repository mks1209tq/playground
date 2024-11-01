<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmiratesIdStoreRequest;
use App\Http\Requests\EmiratesIdUpdateRequest;
use App\Models\EmiratesId;
use App\Services\MrzParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\Storage;


class EmiratesIdController extends Controller
{
    public function index(Request $request): View
    {
        $emiratesIds = EmiratesId::all();
        return view('emiratesId.index', compact('emiratesIds'));
    }

    public function create(Request $request): View
    {
        return view('emiratesId.create');
    }

    public function store(EmiratesIdStoreRequest $request): RedirectResponse
    {
        $emiratesId = EmiratesId::create($request->validated());
        $request->session()->flash('emiratesId.id', $emiratesId->id);
        return redirect()->route('emiratesIds.index');
    }

    public function show(Request $request, EmiratesId $emiratesId): View
    {
        return view('emiratesId.show', compact('emiratesId'));
    }

    public function edit(Request $request, EmiratesId $emiratesId): View
    {
        return view('emiratesId.edit', compact('emiratesId'));
    }

    public function update(EmiratesIdUpdateRequest $request, EmiratesId $emiratesId): RedirectResponse
    {
        $emiratesId->update($request->validated());
        $request->session()->flash('emiratesId.id', $emiratesId->id);
        return redirect()->route('emiratesIds.index');
    }

    public function destroy(Request $request, EmiratesId $emiratesId): RedirectResponse
    {
        $emiratesId->delete();
        return redirect()->route('emiratesIds.index');
    }

    public function extractMRZ()
    {
        try {
            \Log::info('Starting MRZ extraction');

            // Create temp directory if it doesn't exist
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            \Log::info('Temp directory created: ' . $tempDir);

            // Define file paths
            $tempFileName = 'temp_' . time() . '.pdf';
            $pdfPath = $tempDir . '/' . $tempFileName;
            \Log::info('PDF path will be: ' . $pdfPath);

            try {
                $contents = Storage::disk('idrive_e2')->get('eid-pdf-Only-MRZ.pdf');
                \Log::info('PDF file size: ' . strlen($contents) . ' bytes');
            } catch (\Exception $e) {
                \Log::error('Failed to get file from iDrive: ' . $e->getMessage());
                return back()->with('error', 'Failed to get file from iDrive: ' . $e->getMessage());
            }

            // Save PDF file
            if (file_put_contents($pdfPath, $contents) === false) {
                \Log::error('Failed to save PDF file to temp directory');
                return back()->with('error', 'Failed to save PDF file');
            }
            \Log::info('PDF saved to: ' . $pdfPath);

            // Use server-compatible path for pdftotext
            $binaryPath = '/usr/bin/pdftotext'; // Linux server path
            if (PHP_OS === 'WINNT') {
                $binaryPath = 'C:/Program Files/Poppler/bin/pdftotext.exe';
            }
            \Log::info('Using binary path: ' . $binaryPath);

            $outputPath = $tempDir . '/output_' . time() . '.txt';
            \Log::info('Output will be saved to: ' . $outputPath);

            // Execute command with proper permissions
            $command = sprintf('"%s" -layout -enc UTF-8 "%s" "%s"', $binaryPath, $pdfPath, $outputPath);
            \Log::info('Executing command: ' . $command);
            
            $output = shell_exec($command . ' 2>&1');
            \Log::info('Command output: ' . ($output ?? 'No output'));

            if (!file_exists($outputPath)) {
                \Log::error('Output file was not created');
                return back()->with('error', 'Failed to create output file');
            }

            $text = file_get_contents($outputPath);
            \Log::info('Extracted text length: ' . strlen($text));
            
            // Cleanup
            @unlink($pdfPath);
            @unlink($outputPath);

            // Clean the text first
            $text = str_replace(["\r", "\f"], "", $text);

            // Extract MRZ using simple line processing
            $lines = explode("\n", $text);
            \Log::info('Number of lines: ' . count($lines));
            
            if (count($lines) >= 2) {
                $firstLine = trim($lines[0]);
                $secondLine = trim($lines[1]);
                
                \Log::info('First line: ' . $firstLine);
                \Log::info('Second line: ' . $secondLine);
                
                if (strpos($firstLine, 'ILARE') === 0) {
                    $mrz = str_replace(' ', '', $firstLine) . $secondLine;
                    
                    // Parse the MRZ using the new MrzParser
                    $parser = new MrzParser($mrz);
                    $parsedData = $this->convertParsedDataToViewFormat($parser->parse());
                    
                    return view('emiratesId.preview', [
                        'mrz' => $mrz,
                        'parsedData' => $parsedData
                    ]);
                }
            }

            return back()->with('error', 'Invalid MRZ format');

        } catch (\Exception $e) {
            \Log::error('Exception in extractMRZ: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', $e->getMessage());
        }
    }

    

    public function parse(Request $request)
    {
        $mrz = $request->input('mrz');
        
    if (!str_starts_with($mrz, 'ILARE')) {
        return back()->with('error', 'Invalid MRZ format');
    }

    try {
        $parser = new MrzParser($mrz);
        $parsed = $parser->parse();
        
        $emiratesId = new EmiratesId();
        $emiratesId->document_type = $parsed['documentType'];
        $emiratesId->country_code = $parsed['authority'];
        $emiratesId->card_number = $parsed['cardNumber'];
        $emiratesId->id_number = $parsed['emiratesId'];
        $emiratesId->date_of_birth = $this->formatMrzDate($parsed['dateOfBirth']);
        $emiratesId->gender = $parsed['gender'] === 'M' ? 'Male' : 'Female';
        $emiratesId->expiry_date = $this->formatMrzDate($parsed['dateOfExpiry']);
        $emiratesId->nationality = $parsed['nationality'];
        $emiratesId->surname = $parsed['surname'];
        $emiratesId->given_names = $parsed['givenNames'];

        $emiratesId->save();

        return redirect()->route('emiratesIds.index')
            ->with('success', 'Emirates ID saved successfully');
        
    } catch (\Exception $e) {
        \Log::error('Error in parse: ' . $e->getMessage());
        return redirect()->back()
            ->with('error', 'Failed to parse MRZ: ' . $e->getMessage());
        }
    }   

    private function convertParsedDataToViewFormat(array $parsed): array
{
    try {
        // Format birth date
        $birthDate = $this->formatMrzDate($parsed['dateOfBirth']);
        $expiryDate = $this->formatMrzDate($parsed['dateOfExpiry']);

        return [
            'document_type' => $parsed['documentType'],
            'country_code' => $parsed['authority'],  // Changed from authorityCode
            'card_number' => $parsed['cardNumber'],
            'id_number' => $parsed['emiratesId'],
            'date_of_birth' => $birthDate,
            'gender' => $parsed['gender'] === 'M' ? 'Male' : 'Female',
            'expiry_date' => $expiryDate,
            'nationality' => $parsed['nationality'],
            'surname' => $parsed['surname'],
            'given_names' => $parsed['givenNames']  // Removed firstName concatenation
        ];
    } catch (\Exception $e) {
        \Log::error('Error in convertParsedDataToViewFormat: ' . $e->getMessage());
        throw $e;
    }
}

private function formatMrzDate(string $dateStr): string
{
    try {
        // Clean the date string
        $dateStr = preg_replace('/[^0-9]/', '', $dateStr);
        
        // Extract components
        $year = substr($dateStr, 0, 2);
        $month = substr($dateStr, 2, 2);
        $day = substr($dateStr, 3, 2);

        // Convert to full year
        $fullYear = (int)$year < 50 ? "20$year" : "19$year";
        
        // Ensure valid month and day
        $month = min(12, max(1, (int)$month));
        $day = min(31, max(1, (int)$day));

        // Format the date
        return sprintf('%02d.%02d.%s', $day, $month, $fullYear);
    } catch (\Exception $e) {
        \Log::error('Date formatting error: ' . $e->getMessage());
        return 'Invalid Date';
        }
    }

    
}
