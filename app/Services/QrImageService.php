<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrImageService
{
    public function png(string $payload, int $size = 300): string
    {
        return QrCode::size($size)
            ->format('png')
            ->errorCorrection('M')
            ->generate($payload);
    }
}
