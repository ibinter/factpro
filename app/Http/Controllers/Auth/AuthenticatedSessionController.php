<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            Auth::logout();

            $request->session()->put('2fa:user_id', $user->id);
            $request->session()->put('2fa:remember', $request->boolean('remember'));

            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Display the two-factor challenge view.
     */
    public function challenge(Request $request): RedirectResponse|Response
    {
        if (! $request->session()->has('2fa:user_id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    /**
     * Verify the two-factor challenge and log the user in.
     */
    public function challengeStore(Request $request, TwoFactorService $twoFactor): RedirectResponse
    {
        $userId = $request->session()->get('2fa:user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $code = $request->input('code');
        $recoveryCode = $request->input('recovery_code');

        $authenticated = false;

        if (filled($code)) {
            $secret = $twoFactor->decryptSecret($user->two_factor_secret);
            $authenticated = $twoFactor->verify($secret, $code);
        } elseif (filled($recoveryCode)) {
            $authenticated = $twoFactor->useRecoveryCode($user, $recoveryCode);
        }

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'code' => 'Code invalide.',
            ]);
        }

        $remember = (bool) $request->session()->get('2fa:remember', false);

        Auth::login($user, $remember);

        $request->session()->forget(['2fa:user_id', '2fa:remember']);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
