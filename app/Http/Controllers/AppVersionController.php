<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    /**
     * Marque la dernière version publiée comme vue par l'utilisateur connecté.
     */
    public function markSeen(Request $request): RedirectResponse
    {
        $request->user()->update(['last_version_seen_at' => now()]);

        return back();
    }
}
