<?php

/*
 * MenuQR — safe image storage.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 *
 * Uploaded images are decoded, resized, re-encoded to WebP and stripped of
 * metadata. SVG and other active formats are never stored.
 */

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImageUploader
{
    private const ALLOWED = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];

    public function store(UploadedFile $file, string $directory, string $field = 'image'): string
    {
        $info = @getimagesize($file->getRealPath());

        if (! is_array($info) || ! in_array($info[2] ?? null, self::ALLOWED, true)) {
            throw ValidationException::withMessages([
                $field => __('menuqr.errors.image_invalid'),
            ]);
        }

        [$width, $height] = $info;

        if ($width < 1 || $height < 1 || $width > 8000 || $height > 8000 || ($width * $height) > 40_000_000) {
            throw ValidationException::withMessages([
                $field => __('menuqr.errors.image_dimensions'),
            ]);
        }

        $contents = file_get_contents($file->getRealPath());
        $source = $contents === false ? false : @imagecreatefromstring($contents);

        if ($source === false) {
            throw ValidationException::withMessages([
                $field => __('menuqr.errors.image_invalid'),
            ]);
        }

        $max = (int) config('menuqr.uploads.max_dimension', 1600);
        $scale = min(1, $max / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($source);

        $temporary = tempnam(sys_get_temp_dir(), 'menuqr-image-');

        if ($temporary === false || ! imagewebp($canvas, $temporary, (int) config('menuqr.uploads.quality', 82))) {
            imagedestroy($canvas);

            throw ValidationException::withMessages([
                $field => __('menuqr.errors.image_invalid'),
            ]);
        }

        imagedestroy($canvas);

        $name = Str::random(40).'.webp';
        $path = trim($directory, '/').'/'.$name;

        Storage::disk('public')->put($path, (string) file_get_contents($temporary));
        @unlink($temporary);

        return $path;
    }
}
