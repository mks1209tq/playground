<?php

namespace App\Services;

use G4T\IdScanner\IdScanner;

class IdScannerService
{
    private $scanner;

    public function __construct(IdScanner $scanner)
    {
        $this->scanner = $scanner;
    }

    public function parseMrz(string $mrz): array
    {
        return $this->scanner->parse($mrz);
    }
}