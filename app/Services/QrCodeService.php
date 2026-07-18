<?php

namespace App\Services;

use App\Models\Document;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService
{
    /** QR d'authenticité en data-URI (PNG base64) pour intégration PDF/HTML. */
    public function forDocument(Document $document, int $scale = 4): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'eccLevel' => EccLevel::M,
            'scale' => $scale,
            'outputBase64' => true,
            'quietzoneSize' => 2,
        ]);

        return (new QRCode($options))->render($document->verificationUrl());
    }
}
