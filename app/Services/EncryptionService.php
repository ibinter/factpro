<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    // Champs à chiffrer par modèle
    const SENSITIVE_FIELDS = [
        'customers' => ['email', 'phone', 'address', 'tax_number'],
        'employees' => ['cnss_number', 'bank_account', 'national_id'],
        'contacts'  => ['email', 'phone'],
    ];

    public function encrypt(string $value): string
    {
        return Crypt::encryptString($value);
    }

    public function decrypt(string $value): ?string
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value; // already plain (legacy)
        }
    }

    public function isEncrypted(string $value): bool
    {
        // Laravel encrypted strings start with 'eyJ' (base64 JSON)
        return str_starts_with($value, 'eyJ');
    }
}
