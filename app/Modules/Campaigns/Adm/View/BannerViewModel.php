<?php
namespace Coyote\Modules\Campaigns\Adm\View;

readonly class BannerViewModel {
    public function __construct(
        public int    $views,
        public int    $clicks,
        public string $bannerSrc,
    ) {}

    public function ctr(): string {
        if ($this->views === 0) {
            return '?';
        }
        return $this->percentage($this->clicks / $this->views);
    }

    private function percentage(float $rate): string {
        return number_format($rate * 100, 3) . '%';
    }
}
