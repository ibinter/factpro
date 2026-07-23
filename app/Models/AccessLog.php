<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AccessLog extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Record an access log entry for the current user/request.
     */
    public static function record(string $action, bool $success = true, ?string $notes = null): void
    {
        $user = Auth::user();

        static::create([
            'user_id'    => $user?->id,
            'company_id' => $user?->current_company_id,
            'action'     => $action,
            'ip_address' => Request::ip(),
            'user_agent' => substr(Request::userAgent() ?? '', 0, 500),
            'country'    => null,
            'success'    => $success,
            'notes'      => $notes,
        ]);
    }
}
