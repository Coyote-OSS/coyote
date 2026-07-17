<?php
namespace Test\Modules\Campaigns\Fixture;

use Modules\Campaigns\ForRotatingBanners;

class TestRotatingBanners implements ForRotatingBanners {
    private int $index = 0;

    public function rotate(): void {
        $this->index++;
    }

    public function rotationSeed(): int {
        return $this->index;
    }
}
