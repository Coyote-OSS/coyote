<?php
namespace Modules\Campaigns;

class SlidingWindow {
    public function slide(array $array, int $length, int $offset): array {
        if (empty($array)) {
            return [];
        }
        $count = \count($array);
        $rotated = [
            ...\array_slice($array, $offset % $count),
            ...\array_slice($array, 0, $offset % $count),
        ];
        return \array_slice($rotated, 0, \min($count, $length));
    }
}
