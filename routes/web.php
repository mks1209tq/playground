<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmiratesIdController;

use App\Http\Controllers\IdScannerController;
use App\Http\Controllers\OcrController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/emirates-id/extract-mrz', [EmiratesIdController::class, 'extractMRZ'])->name('emirates-id.extractMRZ');
Route::post('/emirates-id/parse', [EmiratesIdController::class, 'parse'])->name('emirates-id.parse');


Route::post('/scan-id', [IdScannerController::class, 'scan'])->name('scan-id');

Route::get('/scan-mrz', [OcrController::class, 'scanMrz'])->name('scan-Mrz');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


Route::resource('emirates-id', EmiratesIdController::class);



Route::get('/tp', function() {
    $output = shell_exec('"C:\Program Files\Poppler\bin\pdftotext.exe" -v 2>&1');
    return $output ? "pdftotext output: $output" : "pdftotext not accessible";
});

Route::get('/id-scanner-test', function () {
    return view('id-scanner-test');
});

Route::get('/imagick', function () {
    return view('imagick');
});
