<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class SecurityPolicy extends Model
{
    protected $guarded = [];

    protected $casts = [
        'allowed_ips'               => 'array',
        'password_require_uppercase' => 'boolean',
        'password_require_number'    => 'boolean',
        'password_require_symbol'    => 'boolean',
        'single_session'             => 'boolean',
        'require_2fa'                => 'boolean',
        'log_all_access'             => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Validate a password against this policy.
     * Returns ['valid' => bool, 'errors' => string[]]
     */
    public function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < $this->password_min_length) {
            $errors[] = "Le mot de passe doit contenir au moins {$this->password_min_length} caractères.";
        }

        if ($this->password_require_uppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule.';
        }

        if ($this->password_require_number && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre.';
        }

        if ($this->password_require_symbol && !preg_match('/[\W_]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un symbole.';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Check if an IP is allowed by the whitelist.
     * If no whitelist, all IPs are allowed.
     */
    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->allowed_ips)) {
            return true;
        }

        return in_array($ip, $this->allowed_ips);
    }
}
