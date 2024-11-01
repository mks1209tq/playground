<?php
require_once 'vendor/autoload.php';
use thiagoalessio\TesseractOCR\TesseractOCR;

$pdf_file = storage_path('app/eid/eida_784196841952581.pdf');
$output_file = storage_path('app/eid/output.txt');

// Convert PDF to images (requires ImageMagick)
$convert_command = "magick convert -density 300 $pdf_file -depth 8 temp_%04d.png";
exec($convert_command);

// Perform OCR on each image
$ocr = new TesseractOCR('temp_0001.png');
$text = $ocr->run();

file_put_contents($output_file, $text);

echo "OCR completed. Results saved in $output_file";

// Clean up temporary files
array_map('unlink', glob("temp_*.png"));
?>