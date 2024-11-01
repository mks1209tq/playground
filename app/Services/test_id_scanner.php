<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Services\IdScannerService;

// Your MRZ string
$mrz = "ILARE13387797307842000216996060007135M2512071IND<<<<<<<<<<<4JAYAKUMAR<JAYAKUMAR<<PRASANTHA";

// Create an instance of IdScannerService
$scannerService = new IdScannerService();

// Parse the MRZ
$result = $scannerService->parseMrz($mrz);

// Print the result
print_r($result);