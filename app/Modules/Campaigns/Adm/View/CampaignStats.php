<?php
namespace Coyote\Modules\Campaigns\Adm\View;

readonly class CampaignStats {
    public function __construct(
        public int $views,
        public int $clicks,
    ) {}

    public function concat(self $other): self {
        return new self(
            $this->views + $other->views,
            $this->clicks + $other->clicks);
    }

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
