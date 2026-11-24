<?php

/*
 * MenuQR - QrCodeService.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Services;

use App\Models\RestaurantTable;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    public function svg(RestaurantTable $table, int $size = 320): string
    {
        $qrCode = new QrCode(
            data: $table->publicUrl(),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: $size,
            margin: 8,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(35, 32, 28),
            backgroundColor: new Color(255, 255, 255),
        );

        return (new SvgWriter)->write($qrCode)->getString();
    }

    public function dataUri(RestaurantTable $table, int $size = 320): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($this->svg($table, $size));
    }
}
