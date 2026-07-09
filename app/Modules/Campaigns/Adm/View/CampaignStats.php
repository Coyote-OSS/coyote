<?php
namespace Coyote\Modules\Campaigns\Adm\View;

readonly class CampaignStats {
    public function __construct(
        public int $views,
        public int $clicks,
        public int $exposures,
    ) {}

    public function concat(self $other): self {
        return new self(
            $this->views + $other->views,
            $this->clicks + $other->clicks,
            $this->exposures + $other->exposures);
    }

    public function ctr(): ?string {
        if ($this->exposures === 0) {
            return null;
        }
        return $this->percentage($this->clicks / $this->exposures);
    }

    private function percentage(float $rate): string {
        return number_format($rate * 100, 3) . '%';
    }
}
