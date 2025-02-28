<?php
namespace Neon\Test\Internal\Color;

readonly class Color
{
    public static function parseRgbaAsHex(string $rgba): string
    {
        [$r, $g, $b, $alpha] = self::digits($rgba);
        if ($alpha === 1) {
            return self::hexRgb($r, $g, $b);
        }
        return self::hexRgb($r, $g, $b) . '00';
    }

    private static function hexRgb(string $r, string $g, string $b): string
    {
        return '#' . self::hexDigit($r) . self::hexDigit($g) . self::hexDigit($b);
    }

    private static function hexDigit(string $r): string
    {
        $hex = \decHex($r);
        if (\strLen($hex) === 1) {
            return "0$hex";
        }
        return $hex;
    }

    private static function digits(string $rgba): array
    {
        \preg_match_all('/\d+/', $rgba, $matches);
        return \array_map(fn(string $digit): int => $digit, $matches[0]);
    }
}
