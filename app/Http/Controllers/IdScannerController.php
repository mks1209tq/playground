<?php

namespace App\Http\Controllers;

use App\Services\IdScannerService;
use Illuminate\Http\Request;

class IdScannerController extends Controller
{
    private $scannerService;

    public function __construct(IdScannerService $scannerService)
    {
        $this->scannerService = $scannerService;
    }

    public function scan(Request $request)
    {
        // Add this debug line
        dd($request->all(), $this->scannerService);

        // ... rest of your method
    }
}