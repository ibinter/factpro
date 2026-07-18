<?php

namespace App\Services;

use App\Models\User;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    protected Google2FA $engine;

    public function __construct()
    {
        $this->engine = new Google2FA();
    }

    /** Nom affiché dans l'application d'authentification. */
    public const ISSUER = 'IBIG FactPro';

    /**
     * Génère un nouveau secret TOTP (base32).
     */
    public function generateSecret(): string
    {
        return $this->engine->generateSecretKey();
    }

    /**
     * Construit l'URL de provisioning otpauth:// pour le secret donné.
     */
    public function provisioningUrl(User $user, string $secret): string
    {
        $issuer = rawurlencode(self::ISSUER);
        $label = $issuer.':'.rawurlencode($user->email);

        return 'otpauth://totp/'.$label
            .'?secret='.$secret
            .'&issuer='.$issuer;
    }

    /**
     * Rend l'URL otpauth en QR code data-URI (PNG base64) via chillerlan.
     */
    public function qrDataUri(string $url): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'eccLevel' => EccLevel::M,
            'scale' => 5,
            'outputBase64' => true,
            'quietzoneSize' => 2,
        ]);

        return (new QRCode($options))->render($url);
    }

    /**
     * Vérifie un code TOTP à 6 chiffres contre le secret (fenêtre de 1).
     */
    public function verify(string $secret, string $code): bool
    {
        return $this->engine->verifyKey($secret, $code, 1);
    }

    /**
     * Génère 8 codes de récupération au format XXXX-XXXX-XX.
     *
     * @return array<int, string>
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::upper(
                Str::random(4).'-'.Str::random(4).'-'.Str::random(2)
            ))
            ->all();
    }

    /**
     * Consomme un code de récupération valide pour l'utilisateur.
     * Retourne true si le code existait et a été supprimé.
     */
    public function useRecoveryCode(User $user, string $code): bool
    {
        if (is_null($user->two_factor_recovery_codes)) {
            return false;
        }

        $codes = json_decode(
            Crypt::decryptString($user->two_factor_recovery_codes),
            true
        ) ?: [];

        $code = Str::upper(trim($code));

        if (! in_array($code, $codes, true)) {
            return false;
        }

        $remaining = array_values(array_filter(
            $codes,
            fn ($stored) => ! hash_equals($stored, $code)
        ));

        $user->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($remaining)),
        ])->save();

        return true;
    }

    /**
     * Chiffre un secret pour stockage.
     */
    public function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    /**
     * Déchiffre un secret stocké.
     */
    public function decryptSecret(string $encrypted): string
    {
        return Crypt::decryptString($encrypted);
    }

    /**
     * Chiffre un tableau de codes de récupération pour stockage.
     *
     * @param  array<int, string>  $codes
     */
    public function encryptRecoveryCodes(array $codes): string
    {
        return Crypt::encryptString(json_encode($codes));
    }

    /**
     * Déchiffre les codes de récupération stockés.
     *
     * @return array<int, string>
     */
    public function decryptRecoveryCodes(string $encrypted): array
    {
        return json_decode(Crypt::decryptString($encrypted), true) ?: [];
    }
}
