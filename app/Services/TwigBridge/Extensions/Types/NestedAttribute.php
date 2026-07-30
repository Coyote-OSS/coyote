<?php
namespace Coyote\Services\TwigBridge\Extensions\Types;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Unlike a Twig macro - which always returns its rendered output wrapped in Twig\Markup -
 * this resolves a dotted attribute path and hands back the underlying PHP value untouched,
 * so callers can still do type-sensitive things with it (e.g. |number_format, is null).
 */
class NestedAttribute extends AbstractExtension {
    public function getFunctions(): array {
        return [
            new TwigFunction('nested_attribute', $this->nestedAttribute(...)),
        ];
    }

    public function nestedAttribute(mixed $object, string $path): mixed {
        $value = $object;
        foreach (explode('.', $path) as $key) {
            if ($value === null) {
                return null;
            }
            $value = $this->attribute($value, trim($key));
        }
        return $value;
    }

    private function attribute(mixed $value, string $key): mixed {
        if (is_array($value)) {
            return $value[$key] ?? null;
        }
        if (is_object($value)) {
            if (property_exists($value, $key)) {
                return $value->$key;
            }
            if (method_exists($value, $key)) {
                return $value->$key();
            }
        }
        return null;
    }
}
