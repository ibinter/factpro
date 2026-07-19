<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DemoController extends Controller
{
    public function login()
    {
        $user = User::where('email', 'demo@factpro.test')->first();

        if (! $user) {
            return redirect('/')->with('error', 'Compte démo non disponible. Contactez-nous.');
        }

        Auth::login($user, remember: false);

        session()->regenerate();

        return redirect()->route('dashboard');
    }
}
