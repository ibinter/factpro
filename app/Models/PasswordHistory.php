<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class PasswordHistory extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a password was recently used.
     */
    public static function isReused(User $user, string $newPassword, int $historyCount): bool
    {
        if ($historyCount <= 0) {
            return false;
        }

        $recent = static::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($historyCount)
            ->pluck('password_hash');

        foreach ($recent as $hash) {
            if (Hash::check($newPassword, $hash)) {
                return true;
            }
        }

        return false;
    }
}
