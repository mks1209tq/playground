<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmiratesIdController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/extract-mrz', [EmiratesIdController::class, 'extractMRZ']);
Route::get('/parse', [EmiratesIdController::class, 'parse']);


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


Route::resource('emirates-ids', EmiratesIdController::class);



Route::get('/tp', function() {
    $output = shell_exec('"C:\Program Files\Poppler\bin\pdftotext.exe" -v 2>&1');
    return $output ? "pdftotext output: $output" : "pdftotext not accessible";
});