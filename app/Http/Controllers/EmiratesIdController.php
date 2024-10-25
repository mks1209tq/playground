<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmiratesIdStoreRequest;
use App\Http\Requests\EmiratesIdUpdateRequest;
use App\Models\EmiratesId;
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

    public function parse(Request $request): JsonResponse
{
    $mrz = $request->input('mrz');
    
    // Validate MRZ format
    if (!str_starts_with($mrz, 'ILARE')) {
        return response()->json(['error' => 'Invalid MRZ format'], 400);
    }

    $emiratesId = new EmiratesId();

    // Parse first part (previously first line)
    $firstPart = substr($mrz, 0, 44); // Adjust length as needed
    $emiratesId->document_type = substr($firstPart, 0, 1);
    $emiratesId->country_code = substr($firstPart, 1, 3);
    $emiratesId->card_number = substr($firstPart, 4, 10);
    $emiratesId->id_number = '784-' . substr($firstPart, 14, 15);

    // Parse second part (previously second line)
    $secondPart = substr($mrz, 44); // Get the rest of the string
    $dobString = substr($secondPart, 0, 6);
    $emiratesId->date_of_birth = Carbon::createFromFormat('ymd', $dobString)->format('d.m.Y');
    $emiratesId->gender = (substr($secondPart, 7, 1) === 'M') ? 'Male' : 'Female';
    $expiryString = substr($secondPart, 8, 6);
    $emiratesId->expiry_date = Carbon::createFromFormat('ymd', $expiryString)->format('d.m.Y');
    $emiratesId->nationality = substr($secondPart, 15, 3);

    // Parse name (previously third line)
    $namePart = substr($secondPart, 44); // Adjust offset as needed
    $nameParts = explode('<', $namePart, 2);
    $emiratesId->surname = str_replace('<', ' ', $nameParts[0]);
    $emiratesId->given_names = str_replace('<', ' ', $nameParts[1] ?? '');

    // Save to database
    $emiratesId->save();

        return response()->json($emiratesId, 201);
    }

    public function extractMRZ()
    {
    try {
        // Create temp directory if it doesn't exist
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Get the file contents directly instead of downloading
        $tempFileName = 'temp_' . time() . '.pdf';
        $pdfPath = $tempDir . '/' . $tempFileName;
        
        // Get contents and save locally
        $contents = Storage::disk('idrive_e2')->get('eid-pdf-Only-MRZ.pdf');
        file_put_contents($pdfPath, $contents);

        // Use server-compatible path for pdftotext
        $binaryPath = '/usr/bin/pdftotext'; // Linux server path
        if (PHP_OS === 'WINNT') {
            $binaryPath = 'C:/Program Files/Poppler/bin/pdftotext.exe';
        }

        $outputPath = $tempDir . '/output_' . time() . '.txt';

        // Execute command with proper permissions
        $command = sprintf('"%s" -layout -enc UTF-8 "%s" "%s"', $binaryPath, $pdfPath, $outputPath);
        $output = shell_exec($command . ' 2>&1');

        if (file_exists($outputPath)) {
            $text = file_get_contents($outputPath);
            
            // Cleanup
            @unlink($pdfPath);
            @unlink($outputPath);

            // Clean the text first
            $text = str_replace(["\r", "\f"], "", $text);

            // Updated MRZ pattern to match your specific format
            $lines = explode("\n", $text);
            if (count($lines) >= 2) {
                $firstLine = trim($lines[0]);
                $secondLine = trim($lines[1]);
                
                if (strpos($firstLine, 'ILARE') === 0) {
        $mrz = str_replace(' ', '', $firstLine) . $secondLine;
                } else {
                    $mrz = "MRZ not found in the PDF";
                }
            } else {
                $mrz = "MRZ not found in the PDF";
            }

            return response()->json([
                'mrz' => $mrz,
                'textPreview' => substr($text, 0, 1000),
                'textLength' => strlen($text),
                'cleanedText' => $text
            ]);
        }

        return response()->json([
            'error' => 'Failed to extract text',
            'command' => $command,
            'output' => $output
        ], 500);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
}

}
