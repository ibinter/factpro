<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function __construct(protected TwoFactorService $twoFactor)
    {
    }

    /**
     * Démarre l'activation : génère un secret (stocké chiffré en session,
     * PAS en base avant confirmation) et retourne le QR + le secret.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                'two_factor' => 'La double authentification est déjà activée.',
            ]);
        }

        $secret = $this->twoFactor->generateSecret();

        $request->session()->put('2fa_setup_secret', $this->twoFactor->encryptSecret($secret));

        $url = $this->twoFactor->provisioningUrl($user, $secret);

        return response()->json([
            'qr' => $this->twoFactor->qrDataUri($url),
            'secret' => $secret,
        ]);
    }

    /**
     * Confirme l'activation : vérifie le code 6 chiffres contre le secret
     * de session, puis persiste secret + codes de récupération + confirmed_at.
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();

        $encryptedSecret = $request->session()->get('2fa_setup_secret');

        if (! $encryptedSecret) {
            throw ValidationException::withMessages([
                'code' => 'La session d\'activation a expiré. Recommencez.',
            ]);
        }

        $secret = $this->twoFactor->decryptSecret($encryptedSecret);

        if (! $this->twoFactor->verify($secret, $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => 'Le code saisi est invalide.',
            ]);
        }

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $this->twoFactor->encryptSecret($secret),
            'two_factor_recovery_codes' => $this->twoFactor->encryptRecoveryCodes($recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('2fa_setup_secret');

        return response()->json([
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Désactive la 2FA après vérification du mot de passe.
     */
    public function destroy(Request $request): JsonResponse
    {
        $this->ensureCurrentPassword($request);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return response()->json(['status' => 'disabled']);
    }

    /**
     * Régénère les codes de récupération après vérification du mot de passe.
     */
    public function recoveryCodes(Request $request): JsonResponse
    {
        $this->ensureCurrentPassword($request);

        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            throw ValidationException::withMessages([
                'two_factor' => 'La double authentification n\'est pas activée.',
            ]);
        }

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => $this->twoFactor->encryptRecoveryCodes($recoveryCodes),
        ])->save();

        return response()->json([
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Vérifie le mot de passe courant ou lève une 422.
     */
    protected function ensureCurrentPassword(Request $request): void
    {
        $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->input('current_password'), $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Le mot de passe est incorrect.',
            ]);
        }
    }
}
