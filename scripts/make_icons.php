<?php
/**
 * Utilitaire jetable : génère les icônes PWA IBIG FactPro avec GD.
 * Usage : php scripts/make_icons.php
 */

$outDir = __DIR__ . '/../public/icons';
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

/**
 * Vérifie qu'un pixel est à l'intérieur d'un carré à coins arrondis.
 */
function insideRounded(int $x, int $y, int $size, int $radius): bool
{
    if ($radius <= 0) {
        return true;
    }
    $max = $size - 1;
    // Coins : centres des cercles
    $corners = [
        [$radius, $radius],
        [$max - $radius, $radius],
        [$radius, $max - $radius],
        [$max - $radius, $max - $radius],
    ];
    // Zone centrale (croix) toujours dedans
    if (($x >= $radius && $x <= $max - $radius) || ($y >= $radius && $y <= $max - $radius)) {
        return true;
    }
    foreach ($corners as [$cx, $cy]) {
        $dx = $x - $cx;
        $dy = $y - $cy;
        if ($dx * $dx + $dy * $dy <= $radius * $radius) {
            return true;
        }
    }
    return false;
}

/**
 * Génère une icône.
 *
 * @param int    $size     taille en px
 * @param string $file     chemin de sortie
 * @param string $mode     'rounded' (coins arrondis, fond transparent hors coins)
 *                         ou 'fullbleed' (carré plein, pour maskable / apple)
 * @param float  $glyphScale part de la taille occupée par le contenu (zone de sécurité)
 */
function makeIcon(int $size, string $file, string $mode, float $glyphScale): void
{
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    imagealphablending($img, false);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);

    $radius = $mode === 'rounded' ? (int) round($size * 0.22) : 0;

    // Dégradé vertical bleu #0062CC -> marine #002D5B, ligne par ligne
    $c1 = [0x00, 0x62, 0xCC];
    $c2 = [0x00, 0x2D, 0x5B];
    for ($y = 0; $y < $size; $y++) {
        $t = $size > 1 ? $y / ($size - 1) : 0;
        $r = (int) round($c1[0] + ($c2[0] - $c1[0]) * $t);
        $g = (int) round($c1[1] + ($c2[1] - $c1[1]) * $t);
        $b = (int) round($c1[2] + ($c2[2] - $c1[2]) * $t);
        $col = imagecolorallocatealpha($img, $r, $g, $b, 0);
        for ($x = 0; $x < $size; $x++) {
            if ($radius > 0 && !insideRounded($x, $y, $size, $radius)) {
                continue;
            }
            imagesetpixel($img, $x, $y, $col);
        }
    }

    // Dessin du glyphe par-dessus le fond
    imagealphablending($img, true);
    $white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);
    $gold  = imagecolorallocate($img, 0xF0, 0xC0, 0x40);

    // Grille de conception 48x48 dans la zone de contenu (glyphScale centré)
    $content = $size * $glyphScale;
    $offset  = ($size - $content) / 2;
    $u = $content / 48.0;
    $rect = function (float $gx1, float $gy1, float $gx2, float $gy2, int $color) use ($img, $offset, $u) {
        imagefilledrectangle(
            $img,
            (int) round($offset + $gx1 * $u),
            (int) round($offset + $gy1 * $u),
            (int) round($offset + $gx2 * $u) - 1,
            (int) round($offset + $gy2 * $u) - 1,
            $color
        );
    };

    // Lettre "F" géométrique épaisse
    $rect(13, 10, 20, 38, $white);  // barre verticale
    $rect(13, 10, 34, 17, $white);  // barre horizontale haute
    $rect(13, 22, 30, 28, $gold);   // barre du milieu en or

    // Petit carré or en bas à droite (rappel du QR du logo)
    $rect(30, 31, 38, 39, $gold);

    imagepng($img, $file, 9);
    imagedestroy($img);
    echo basename($file) . ' : ' . filesize($file) . " octets\n";
}

makeIcon(192, $outDir . '/icon-192.png', 'rounded', 0.94);
makeIcon(512, $outDir . '/icon-512.png', 'rounded', 0.94);
makeIcon(512, $outDir . '/icon-512-maskable.png', 'fullbleed', 0.76); // zone de sécurité 80%
makeIcon(180, $outDir . '/apple-touch-icon.png', 'fullbleed', 0.86);

// Vérification des dimensions
foreach (['icon-192.png' => 192, 'icon-512.png' => 512, 'icon-512-maskable.png' => 512, 'apple-touch-icon.png' => 180] as $f => $expected) {
    $info = getimagesize($outDir . '/' . $f);
    $ok = $info && $info[0] === $expected && $info[1] === $expected;
    echo $f . ' => ' . ($info ? "{$info[0]}x{$info[1]}" : 'ERREUR') . ($ok ? ' OK' : ' KO') . "\n";
    if (!$ok) {
        exit(1);
    }
}
echo "Toutes les icônes générées.\n";
