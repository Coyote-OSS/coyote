<?php
namespace Test\Modules\Campaigns\Fixture;

use Modules\Campaigns\ForUserVoivodeship;
use Modules\Campaigns\Voivodeship;

class TestUserVoivodeship implements ForUserVoivodeship {
    private ?Voivodeship $voivodeship;

    public function stubVoivodeship(?Voivodeship $voivodeship): void {
        $this->voivodeship = $voivodeship;
    }

    public function currentUserVoivodeship(): ?Voivodeship {
        return $this->voivodeship;
    }
}
