<?php

namespace App\Services;

class MrzParser
{
    private string $mrzString;
    private array $parsed = [];

    // Constants for MRZ field lengths
    private const DOCUMENT_TYPE_LENGTH = 1;
    private const AUTHORITY_CODE_LENGTH = 4;
    private const CARD_NUMBER_LENGTH = 9;
    private const EMIRATES_ID_LENGTH = 15;  // Changed from 15 to 14
    private const DATE_LENGTH = 6;
    private const GENDER_LENGTH = 1;
    private const NATIONALITY_LENGTH = 3;

    public function __construct(string $mrzString)
    {
        // Clean up the input string
        $this->mrzString = preg_replace('/\s+/', '', $mrzString);
    }

    public function parse(): array
    {
        try {
            // Validate MRZ string length
            if (strlen($this->mrzString) < 90) {
                throw new \Exception('Invalid MRZ string length');
            }

            \Log::info('Parsing MRZ: ' . $this->mrzString);

            $this->parsed = [
                'documentType' => $this->extractField(0, self::DOCUMENT_TYPE_LENGTH),
                'authority' => $this->extractField(1, self::AUTHORITY_CODE_LENGTH),
                'cardNumber' => $this->extractField(5, self::CARD_NUMBER_LENGTH),
                'emiratesId' => $this->formatEmiratesId(
                    $this->extractField(15, self::EMIRATES_ID_LENGTH)  // Changed starting position to 15
                ),
                'checkDigit1' => $this->extractField(29, 1),
                'dateOfBirth' => $this->extractField(30, 5),
                'gender' => $this->extractField(37, self::GENDER_LENGTH),
                'dateOfExpiry' => $this->extractField(38, 6),
                'checkDigit2' => $this->extractField(44, 1),
                'nationality' => $this->extractField(45, self::NATIONALITY_LENGTH),
                'optionalData' => $this->extractField(48, 11),
                'checkDigit3' => $this->extractField(59, 1),
                'surname' => $this->cleanName($this->extractField(60, 9)),
                'givenNames' => $this->cleanName($this->extractField(69))
            ];

            // Validate check digits
            $this->validateCheckDigits();

            return $this->parsed;
        } catch (\Exception $e) {
            \Log::error('MRZ parsing error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function extractField(int $offset, ?int $length = null): string
    {
        try {
            if ($length === null) {
                return substr($this->mrzString, $offset);
            }
            $field = substr($this->mrzString, $offset, $length);
            if ($field === false) {
                throw new \Exception("Failed to extract field at offset $offset");
            }
            return $field;
        } catch (\Exception $e) {
            \Log::error("Field extraction error at offset $offset: " . $e->getMessage());
            throw $e;
        }
    }

    private function formatEmiratesId(string $id): string
    {
        // Input: 784200021699606
        // Expected Output: 784-2000-21699606
        return sprintf(
            '%s-%s-%s',
            substr($id, 0, 3),     // 784
            substr($id, 3, 4),     // 2000
            substr($id, 7)         // 21699606
        );
    }

    private function cleanName(string $name): string
    {
        // Remove filler characters and trim
        return trim(str_replace('<', ' ', $name));
    }

    private function validateCheckDigits(): void
    {
        // Implement check digit validation according to ICAO rules
        $this->validateCheckDigit(
            $this->parsed['cardNumber'], 
            $this->parsed['checkDigit1']
        );
    }

    private function validateCheckDigit(string $field, string $checkDigit): void
    {
        // Implementation of the ICAO check digit algorithm
        // Weights for each position (3,7,1 sequence)
        $weights = [7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1];
        
        $sum = 0;
        $fieldChars = str_split($field);
        
        foreach ($fieldChars as $i => $char) {
            if (isset($weights[$i])) {
                $value = is_numeric($char) ? (int)$char : (ord($char) - 55);
                $sum += $value * $weights[$i];
            }
        }

        $calculatedCheckDigit = (string)($sum % 10);
        if ($calculatedCheckDigit !== $checkDigit) {
            \Log::warning("Check digit mismatch: expected $checkDigit, got $calculatedCheckDigit");
        }
    }
}