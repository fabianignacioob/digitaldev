<?php
declare(strict_types=1);

namespace App\Service;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use InvalidArgumentException;

class SiteQrCodeService
{
    public function svg(string $url): string
    {
        $parts = parse_url($url);
        if (!is_array($parts) || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host'])) {
            throw new InvalidArgumentException('La URL pública del sitio no es válida para generar el código QR.');
        }

        $qrCode = new QrCode(
            data: $url,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 600,
            margin: 12,
        );

        return (new SvgWriter())->write($qrCode)->getString();
    }
}
