<?php
namespace Libs\Arrays;

readonly class arrays {
    private function __construct() {}

    public static function filter(callable $filter): callable {
        return fn(array $array): array => \array_values(
            \array_filter($array, fn($item) => self::callPredicate($filter, $item)),
        );
    }

    public static function map(callable $mapper): callable {
        return fn(array $array): array => \array_map($mapper, $array, \array_keys($array));
    }

    public static function values(): callable {
        return \array_values(...);
    }

    private static function callPredicate(callable $predicate, mixed $item): bool {
        $result = $predicate($item);
        if (\is_bool($result)) {
            return $result;
        }
        throw new \TypeError();
    }
}
