<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$sourcePath = $projectRoot.'/public/images/logo-edsp.png';
$outputDirectory = $projectRoot.'/public/images/pwa';

if (! extension_loaded('gd')) {
    fwrite(STDERR, "L’extension PHP GD est requise pour générer les icônes PWA.\n");
    exit(1);
}

$source = imagecreatefrompng($sourcePath);

if ($source === false) {
    fwrite(STDERR, "Impossible de lire le logo EDSP : {$sourcePath}\n");
    exit(1);
}

if (! is_dir($outputDirectory) && ! mkdir($outputDirectory, 0755, true) && ! is_dir($outputDirectory)) {
    fwrite(STDERR, "Impossible de créer le dossier : {$outputDirectory}\n");
    exit(1);
}

function fillRoundedRectangle(
    GdImage $canvas,
    int $x,
    int $y,
    int $width,
    int $height,
    int $radius,
    int $color,
): void {
    imagefilledrectangle($canvas, $x + $radius, $y, $x + $width - $radius, $y + $height, $color);
    imagefilledrectangle($canvas, $x, $y + $radius, $x + $width, $y + $height - $radius, $color);
    imagefilledellipse($canvas, $x + $radius, $y + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($canvas, $x + $width - $radius, $y + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($canvas, $x + $radius, $y + $height - $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($canvas, $x + $width - $radius, $y + $height - $radius, $radius * 2, $radius * 2, $color);
}

function generateIcon(GdImage $source, string $path, int $size, float $cardRatio, float $logoRatio): void
{
    $canvas = imagecreatetruecolor($size, $size);
    $navy = imagecolorallocate($canvas, 11, 31, 85);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefill($canvas, 0, 0, $navy);

    $cardSize = (int) round($size * $cardRatio);
    $cardOffset = (int) round(($size - $cardSize) / 2);
    $cardRadius = max(8, (int) round($size * 0.09));
    fillRoundedRectangle($canvas, $cardOffset, $cardOffset, $cardSize, $cardSize, $cardRadius, $white);

    $sourceWidth = imagesx($source);
    $sourceHeight = imagesy($source);
    $maximumLogoWidth = $size * $logoRatio;
    $maximumLogoHeight = $size * $logoRatio;
    $scale = min($maximumLogoWidth / $sourceWidth, $maximumLogoHeight / $sourceHeight);
    $logoWidth = (int) round($sourceWidth * $scale);
    $logoHeight = (int) round($sourceHeight * $scale);
    $logoX = (int) round(($size - $logoWidth) / 2);
    $logoY = (int) round(($size - $logoHeight) / 2);

    imagecopyresampled(
        $canvas,
        $source,
        $logoX,
        $logoY,
        0,
        0,
        $logoWidth,
        $logoHeight,
        $sourceWidth,
        $sourceHeight,
    );
    imagepng($canvas, $path, 9);
    imagedestroy($canvas);
}

generateIcon($source, $outputDirectory.'/icon-192.png', 192, 0.84, 0.72);
generateIcon($source, $outputDirectory.'/icon-512.png', 512, 0.84, 0.72);
generateIcon($source, $outputDirectory.'/maskable-512.png', 512, 0.68, 0.58);
generateIcon($source, $outputDirectory.'/apple-touch-icon.png', 180, 0.84, 0.72);

imagedestroy($source);

fwrite(STDOUT, "Icônes PWA générées dans {$outputDirectory}\n");
