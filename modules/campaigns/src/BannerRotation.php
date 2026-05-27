<?php
namespace Modules\Campaigns;

readonly class BannerRotation {
    /**
     * @param string[] $keys
     * @return string[]
     */
    public function rotatedBanners(array $keys, int $amount, int $index): array {
        if ($amount < 0) {
            throw new \Exception();
        }
        if (count($keys) < $amount) {
            return $keys;
        }
        if (count($keys) === 0) {
            return [];
        }
        $start = $index % count($keys);
        $left = \array_slice($keys, 0, $start);
        $right = \array_slice($keys, $start);
        return \array_slice(\array_merge($right, $left), 0, $amount);
    }
}
