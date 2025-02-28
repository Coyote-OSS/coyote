<?php
namespace Neon\Test\Integration;

readonly class Color
{
    public static function parseRgbaAsHex(string $cssProperty): string
    {
        if (\preg_match('/^rgba\((\d+), (\d+), (\d+), 1\)$/D', $cssProperty, $match)) {
            [$whole, $r, $g, $b] = $match;
            return self::hex($r, $g, $b);
        }
        throw new \RuntimeException("Failed to parse rgba: $cssProperty");
    }

    private static function hex(string $r, string $g, string $b): string
    {
        return '#' . \decHex($r) . \decHex($g) . \decHex($b);
    }
}
