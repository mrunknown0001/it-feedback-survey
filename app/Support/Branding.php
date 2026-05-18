<?php

namespace App\Support;

use App\Models\Setting;
use Filament\Support\Colors\Color;

class Branding
{
    public const DEFAULT_PRIMARY = '#f97316';

    public const DEFAULT_BRAND_NAME = 'HR Personnel Services';

    public static function primaryHex(): string
    {
        $hex = Setting::get('primary_color', self::DEFAULT_PRIMARY) ?? self::DEFAULT_PRIMARY;

        return self::normalizeHex($hex) ?? self::DEFAULT_PRIMARY;
    }

    public static function brandName(): string
    {
        return Setting::get('brand_name', self::DEFAULT_BRAND_NAME) ?? self::DEFAULT_BRAND_NAME;
    }

    /** Comma-separated "R, G, B" string for use in `rgba(var(--primary-rgb), …)` */
    public static function primaryRgb(): string
    {
        [$r, $g, $b] = self::hexToRgb(self::primaryHex());

        return "{$r}, {$g}, {$b}";
    }

    /** Darker shade for gradients/active states. */
    public static function primaryDark(): string
    {
        return self::shade(self::primaryHex(), -0.18);
    }

    /** Lighter shade for tags/highlights. */
    public static function primaryLight(): string
    {
        return self::shade(self::primaryHex(), 0.30);
    }

    /** Returns the full Tailwind-shade palette used by Filament panels. */
    public static function filamentPalette(): array
    {
        return Color::hex(self::primaryHex());
    }

    private static function normalizeHex(string $hex): ?string
    {
        $hex = trim($hex);
        if (! preg_match('/^#?([0-9a-fA-F]{6}|[0-9a-fA-F]{3})$/', $hex, $m)) {
            return null;
        }
        $val = $m[1];
        if (strlen($val) === 3) {
            $val = $val[0].$val[0].$val[1].$val[1].$val[2].$val[2];
        }

        return '#'.strtolower($val);
    }

    /** @return array{int,int,int} */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function shade(string $hex, float $factor): string
    {
        [$r, $g, $b] = self::hexToRgb($hex);
        if ($factor < 0) {
            $r = (int) round($r * (1 + $factor));
            $g = (int) round($g * (1 + $factor));
            $b = (int) round($b * (1 + $factor));
        } else {
            $r = (int) round($r + (255 - $r) * $factor);
            $g = (int) round($g + (255 - $g) * $factor);
            $b = (int) round($b + (255 - $b) * $factor);
        }

        return sprintf('#%02x%02x%02x', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
    }
}
