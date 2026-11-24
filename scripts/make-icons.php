<?php

/*
 * MenuQR — draw original PWA icons with GD.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 *
 * Run: C:\php84\php.exe scripts\make-icons.php
 */

$targets = [
    ['file' => __DIR__.'/../public/icons/icon-192.png', 'size' => 192, 'maskable' => false],
    ['file' => __DIR__.'/../public/icons/icon-512.png', 'size' => 512, 'maskable' => false],
    ['file' => __DIR__.'/../public/icons/icon-maskable-512.png', 'size' => 512, 'maskable' => true],
];

function roundedRect($im, int $x1, int $y1, int $x2, int $y2, int $radius, int $color): void
{
    imagefilledrectangle($im, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
    imagefilledrectangle($im, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
    imagefilledellipse($im, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($im, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($im, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($im, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
}

function finder($im, int $cx, int $cy, int $outer, int $ring, int $dot, int $color): void
{
    imagefilledrectangle($im, $cx - (int) ($outer / 2), $cy - (int) ($outer / 2), $cx + (int) ($outer / 2), $cy + (int) ($outer / 2), $color);
    $inner = $outer - $ring * 2;
    imagefilledrectangle($im, $cx - (int) ($inner / 2), $cy - (int) ($inner / 2), $cx + (int) ($inner / 2), $cy + (int) ($inner / 2), imagecolorallocate($im, 35, 32, 28));
    imagefilledrectangle($im, $cx - (int) ($dot / 2), $cy - (int) ($dot / 2), $cx + (int) ($dot / 2), $cy + (int) ($dot / 2), $color);
}

foreach ($targets as $target) {
    $size = $target['size'];
    $im = imagecreatetruecolor($size, $size);
    imagealphablending($im, false);
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefilledrectangle($im, 0, 0, $size, $size, $transparent);
    imagealphablending($im, true);

    $charcoal = imagecolorallocate($im, 35, 32, 28);
    $cream = imagecolorallocate($im, 251, 245, 233);
    $saffron = imagecolorallocate($im, 227, 167, 47);
    $olive = imagecolorallocate($im, 169, 183, 122);

    if ($target['maskable']) {
        imagefilledrectangle($im, 0, 0, $size, $size, $charcoal);
    } else {
        roundedRect($im, 0, 0, $size - 1, $size - 1, (int) ($size * 0.18), $charcoal);
    }

    $scale = $size / 512;
    $cx = (int) ($size / 2);
    $base = (int) ($size * ($target['maskable'] ? 0.64 : 0.67));
    $radius = (int) ($size * ($target['maskable'] ? 0.22 : 0.25));

    imagefilledarc($im, $cx, $base, $radius * 2, $radius * 2, 180, 360, $cream, IMG_ARC_PIE);
    imagefilledrectangle($im, $cx - $radius, $base - (int) (2 * $scale), $cx + $radius, $base + (int) (2 * $scale), $cream);

    imagefilledellipse($im, $cx, $base - $radius - (int) (10 * $scale), (int) (18 * $scale), (int) (18 * $scale), $saffron);
    imagefilledrectangle($im, $cx - (int) (3 * $scale), $base - $radius - (int) (22 * $scale), $cx + (int) (3 * $scale), $base - $radius, $saffron);

    finder($im, $cx - (int) (34 * $scale), $base - (int) (34 * $scale), (int) (38 * $scale), (int) (8 * $scale), (int) (12 * $scale), $saffron);
    finder($im, $cx + (int) (34 * $scale), $base - (int) (36 * $scale), (int) (32 * $scale), (int) (7 * $scale), (int) (10 * $scale), $olive);

    imagefilledrectangle($im, $cx - (int) (8 * $scale), $base - (int) (14 * $scale), $cx + (int) (8 * $scale), $base - (int) (8 * $scale), $olive);
    imagefilledrectangle($im, $cx + (int) (18 * $scale), $base - (int) (12 * $scale), $cx + (int) (26 * $scale), $base - (int) (4 * $scale), $saffron);
    imagefilledrectangle($im, $cx - (int) (28 * $scale), $base - (int) (10 * $scale), $cx - (int) (20 * $scale), $base - (int) (4 * $scale), $olive);

    imagepng($im, $target['file']);
    imagedestroy($im);

    echo 'Wrote '.$target['file'].PHP_EOL;
}
