<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\SiteQrCodeService;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

class SiteQrCodeServiceTest extends TestCase
{
    public function testGeneratesSvgAndPngForAValidPublicUrl(): void
    {
        $service = new SiteQrCodeService();

        $svg = $service->svg('https://catops.local/q/1234567890abcdef1234567890abcdef');
        $png = $service->png('https://catops.local/q/1234567890abcdef1234567890abcdef');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertSame("\x89PNG", substr($png, 0, 4));
    }

    public function testRejectsNonPublicUrls(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new SiteQrCodeService())->svg('/q/123');
    }
}
