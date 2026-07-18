<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAuditLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public static function record(
        string $action,
        string $entityType,
        string $entityId,
        ?array $old = null,
        ?array $new = null,
        ?string $reason = null,
        ?int $adminId = null,
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'admin_id' => $adminId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
            'reason' => $reason,
        ]);
    }
}
