<?php
declare(strict_types=1);

namespace App\Service;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use InvalidArgumentException;

class SiteQrCodeService
{
    public function svg(string $url): string
    {
        $qrCode = $this->qrCode($url);

        return (new SvgWriter())->write($qrCode)->getString();
    }

    public function png(string $url): string
    {
        return (new PngWriter())->write($this->qrCode($url))->getString();
    }

    private function qrCode(string $url): QrCode
    {
        $parts = parse_url($url);
        if (!is_array($parts) || !in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host'])) {
            throw new InvalidArgumentException('El enlace público de la vitrina no es válido para generar el código QR.');
        }

        return new QrCode(
            data: $url,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 600,
            margin: 12,
        );
    }
}
