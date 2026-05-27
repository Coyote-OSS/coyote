<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\BannerRotation;
use Modules\Campaigns\ForRotatingBanners;

class TestRotatingBanners implements ForRotatingBanners {
    private int $index = 0;

    public function rotate(): void {
        $this->index++;
    }

    public function rotatedBanners(array $keys, int $amount): array {
        return new BannerRotation()->rotatedBanners($keys, $amount, $this->index);
    }

    public function rotationSeed(): int {
        return $this->index;
    }
}
