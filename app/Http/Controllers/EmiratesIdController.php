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
        $lines = explode("\n", $mrz);

        if (count($lines) !== 3) {
            return response()->json(['error' => 'Invalid MRZ format'], 400);
        }

        $emiratesId = new EmiratesId();

        // Parse first line
        $emiratesId->document_type = substr($lines[0], 0, 1);
        $emiratesId->country_code = substr($lines[0], 1, 3);
        $emiratesId->card_number = substr($lines[0], 4, 10);
        $emiratesId->id_number = '784-' . substr($lines[0], 14, 15);

        // Parse second line
        $dobString = substr($lines[1], 0, 6);
        $emiratesId->date_of_birth = Carbon::createFromFormat('ymd', $dobString)->format('d.m.Y');
        $emiratesId->gender = (substr($lines[1], 7, 1) === 'M') ? 'Male' : 'Female';
        $expiryString = substr($lines[1], 8, 6);
        $emiratesId->expiry_date = Carbon::createFromFormat('ymd', $expiryString)->format('d.m.Y');
        $emiratesId->nationality = substr($lines[1], 15, 3);

        // Parse third line
        $nameParts = explode('<<', $lines[2]);
        $emiratesId->surname = str_replace('<', ' ', $nameParts[0]);
        $emiratesId->given_names = str_replace('<', ' ', $nameParts[1]);

        // Save to database
        $emiratesId->save();

        return response()->json($emiratesId, 201);
    }

    public function extractMRZ()
{
    $pdfPath = storage_path('app/eid/eida_784200021699606.pdf');
    $binaryPath = 'C:/Program Files/Poppler/bin/pdftotext.exe';
    $outputPath = storage_path('app/temp_output.txt');

    if (!file_exists($pdfPath)) {
        return response()->json(['error' => 'PDF file not found'], 404);
    }

    try {
        // Execute pdftotext directly
        $command = '"' . $binaryPath . '" -layout -enc UTF-8 "' . $pdfPath . '" "' . $outputPath . '"';
        $output = shell_exec($command . ' 2>&1');

        if (file_exists($outputPath)) {
            $text = file_get_contents($outputPath);
            unlink($outputPath); // Delete the temporary file

            // MRZ extraction logic
            $mrzPattern = '/[A-Z0-9<]{30,44}\n[A-Z0-9<]{30,44}\n[A-Z0-9<]{30,44}/';
            if (preg_match($mrzPattern, $text, $matches)) {
                $mrz = $matches[0];
            } else {
                $mrz = "MRZ not found in the PDF";
            }

            return response()->json([
                'mrz' => $mrz,
                'textPreview' => substr($text, 0, 1000),
                'textLength' => strlen($text)
            ]);
        } else {
            return response()->json([
                'error' => 'Failed to extract text',
                'command' => $command,
                'output' => $output
            ], 500);
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'binaryPath' => $binaryPath,
            'binaryExists' => file_exists($binaryPath),
            'binaryExecutable' => is_executable($binaryPath)
        ], 500);
        }
    }
}
