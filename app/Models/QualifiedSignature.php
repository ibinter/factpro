<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class QualifiedSignature extends Model
{
    protected $fillable = [
        'company_id',
        'signable_type',
        'signable_id',
        'signer_name',
        'signer_email',
        'signer_role',
        'status',
        'signature_level',
        'token',
        'invited_at',
        'signed_at',
        'expires_at',
        'ip_address',
        'user_agent',
        'signature_data',
        'certificate_hash',
        'audit_trail',
        'otp_code',
        'otp_expires_at',
        'otp_attempts',
        'document_hash',
        'signed_file_path',
    ];

    protected $casts = [
        'audit_trail'    => 'array',
        'expires_at'     => 'datetime',
        'signed_at'      => 'datetime',
        'invited_at'     => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->token)) {
                $model->token = bin2hex(random_bytes(32)); // 64 hex chars
            }
        });
    }

    // ─── Relations ───────────────────────────────────────────────────────────

    public function signable(): MorphTo
    {
        return $this->morphTo();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getIsExpiredAttribute(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isPast();
    }

    // ─── Business methods ────────────────────────────────────────────────────

    public function addAuditEntry(string $action, string $ip): void
    {
        $trail   = $this->audit_trail ?? [];
        $trail[] = [
            'action'    => $action,
            'timestamp' => now()->toISOString(),
            'ip'        => $ip,
        ];
        $this->audit_trail = $trail;
        $this->save();
    }

    public function generateOtp(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->otp_code       = bcrypt($code);
        $this->otp_expires_at = now()->addMinutes(10);
        $this->otp_attempts   = 0;
        $this->save();

        return $code; // plain code to send by email
    }

    public function verifyOtp(string $code): bool
    {
        if ($this->otp_attempts >= 5) {
            return false;
        }
        if (! $this->otp_expires_at || $this->otp_expires_at->isPast()) {
            return false;
        }

        $this->increment('otp_attempts');

        return password_verify($code, $this->otp_code);
    }
}
