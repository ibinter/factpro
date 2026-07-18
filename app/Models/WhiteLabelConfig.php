<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class WhiteLabelConfig extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Trouve la config active basée sur le sous-domaine HTTP_HOST.
     * Retourne null si on est sur le domaine principal.
     */
    public static function forRequest(Request $request): ?self
    {
        $host = $request->getHost();

        // Extraire le sous-domaine (première partie avant le premier point)
        $parts = explode('.', $host);
        if (count($parts) < 2) {
            return null;
        }

        $subdomain = $parts[0];

        // Ignorer les sous-domaines standard (www, app, etc.)
        if (in_array($subdomain, ['www', 'app', 'localhost'])) {
            return null;
        }

        return static::where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();
    }
}
