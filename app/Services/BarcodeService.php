<?php

namespace App\Services;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Throwable;

/**
 * Génération de codes-barres (EAN-13 / Code 128) et QR produits
 * en data-URI PNG pour intégration dans les PDF d'étiquettes (cahier §6.2).
 */
class BarcodeService
{
    /**
     * Code-barres en data-URI PNG.
     * 12-13 chiffres → EAN-13 (checksum complété/validé par la lib),
     * sinon → Code 128. En cas d'échec EAN, fallback Code 128.
     */
    public function barcodePngDataUri(string $code, string $type = 'C128', int $widthFactor = 2, int $height = 40): string
    {
        $code = trim($code);

        if ($code === '') {
            return '';
        }

        $generator = new BarcodeGeneratorPNG;

        // 12 ou 13 chiffres → EAN-13 (la lib complète le checksum sur 12 chiffres).
        if (preg_match('/^\d{12,13}$/', $code)) {
            try {
                $png = $generator->getBarcode($code, BarcodeGeneratorPNG::TYPE_EAN_13, $widthFactor, $height);

                return 'data:image/png;base64,'.base64_encode($png);
            } catch (Throwable) {
                // Checksum invalide ou autre erreur → fallback Code 128 ci-dessous.
            }
        }

        try {
            $barcodeType = $type === 'C128' ? BarcodeGeneratorPNG::TYPE_CODE_128 : $type;
            $png = $generator->getBarcode($code, $barcodeType, $widthFactor, $height);

            return 'data:image/png;base64,'.base64_encode($png);
        } catch (Throwable) {
            try {
                // Dernier recours : Code 128 pur (accepte tout l'ASCII).
                $png = $generator->getBarcode($code, BarcodeGeneratorPNG::TYPE_CODE_128, $widthFactor, $height);

                return 'data:image/png;base64,'.base64_encode($png);
            } catch (Throwable) {
                return '';
            }
        }
    }

    /** QR code en data-URI PNG (chillerlan v6, même config que QrCodeService). */
    public function qrPngDataUri(string $content, int $scale = 3): string
    {
        if (trim($content) === '') {
            return '';
        }

        try {
            $options = new QROptions([
                'outputInterface' => QRGdImagePNG::class,
                'eccLevel' => EccLevel::M,
                'scale' => $scale,
                'outputBase64' => true,
                'quietzoneSize' => 2,
            ]);

            return (new QRCode($options))->render($content);
        } catch (Throwable) {
            return '';
        }
    }
}
