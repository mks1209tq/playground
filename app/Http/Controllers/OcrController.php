<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrController extends Controller
{
    public function scanMrz(Request $request)
    {
        try {
            $pdf_file = storage_path('app/eid/eida_784196841952581.pdf');
            $output_file = storage_path('app/eid/output.txt');
            $temp_dir = storage_path('app/temp');

            // Debug information
            echo "Current working directory: " . getcwd() . "<br>";
            echo "PHP's PATH: " . getenv('PATH') . "<br>";

            // Check if PDF file exists and is readable
            if (!file_exists($pdf_file) || !is_readable($pdf_file)) {
                throw new \Exception("PDF file not found or not readable: $pdf_file");
            }

            // Ensure temp directory exists
            if (!file_exists($temp_dir)) {
                mkdir($temp_dir, 0755, true);
            }

            // ImageMagick conversion
            $magick_path = config('app.imagemagick_path');
            $convert_command = "\"$magick_path\" convert -density 300 \"$pdf_file\" -depth 8 \"$temp_dir/temp_%04d.png\"";
            
            echo "Conversion command: $convert_command<br>";
            
            $output = shell_exec($convert_command . " 2>&1");
            if ($output === null) {
                throw new \Exception("PDF to image conversion failed. Command output: " . $output);
            }

            // Specify the path to the Tesseract executable
            $tesseract_path = config('app.tesseract_path'); 
            echo "Tesseract path: " . $tesseract_path . "<br>";
            if (file_exists($tesseract_path)) {
                echo "Tesseract executable found.<br>";
            } else {
                echo "Tesseract executable not found at specified path.<br>";
            }

            // Perform OCR on all generated images
            $full_text = '';
            foreach (glob("$temp_dir/temp_*.png") as $image_file) {
                $ocr = new TesseractOCR($image_file);
                $ocr->executable($tesseract_path);
                $full_text .= $ocr->run() . "\n\n";
            }

            file_put_contents($output_file, $full_text);

            // Clean up temporary files
            //array_map('unlink', glob("$temp_dir/temp_*.png"));

            return "OCR completed. Results saved in $output_file. Text content: <pre>" . htmlspecialchars($full_text) . "</pre>";
        } catch (\Exception $e) {
            return "An error occurred: " . $e->getMessage();
        }
    }
}