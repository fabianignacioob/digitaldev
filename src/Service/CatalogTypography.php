<?php
declare(strict_types=1);

namespace App\Service;

final class CatalogTypography
{
    public const DEFAULT_FONT = 'Arial, Helvetica, sans-serif';

    /** @var array<string, string> */
    private const OPTIONS = [
        'Arial, Helvetica, sans-serif' => 'Arial',
        'Georgia, serif' => 'Georgia',
        'Verdana, Arial, sans-serif' => 'Verdana',
        'Trebuchet MS, Arial, sans-serif' => 'Trebuchet MS',
        'Courier New, Courier, monospace' => 'Courier New',
    ];

    /** @var array<string, string> */
    private const LEGACY_FONTS = [
        'Inter, Arial, sans-serif' => self::DEFAULT_FONT,
        'Poppins, Arial, sans-serif' => 'Trebuchet MS, Arial, sans-serif',
        'Montserrat, Arial, sans-serif' => 'Verdana, Arial, sans-serif',
    ];

    /** @return array<string, string> */
    public static function options(): array
    {
        return self::OPTIONS;
    }

    public static function normalize(?string $font): string
    {
        $font = trim((string)$font);
        if (isset(self::LEGACY_FONTS[$font])) {
            return self::LEGACY_FONTS[$font];
        }

        return array_key_exists($font, self::OPTIONS) ? $font : self::DEFAULT_FONT;
    }
}
