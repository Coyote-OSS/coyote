<?php
namespace Coyote\Modules\Campaigns\Provided;

use Modules\Campaigns\ForUserVoivodeship;
use Modules\Campaigns\Voivodeship;

class CloudflareVoivodeship implements ForUserVoivodeship {
    private array $cloudflareRegions = [
        'Lower Silesia'      => Voivodeship::Dolnoslaskie,
        'Kujawsko-Pomorskie' => Voivodeship::KujawskoPomorskie,
        'Lublin'             => Voivodeship::Lubelskie,
        'Lubusz'             => Voivodeship::Lubuskie,
        'Łódź Voivodeship'   => Voivodeship::Lodzkie,
        'Lesser Poland'      => Voivodeship::Malopolskie,
        'Mazovia'            => Voivodeship::Mazowieckie,
        'Opole Voivodeship'  => Voivodeship::Opolskie,
        'Subcarpathia'       => Voivodeship::Podkarpackie,
        'Podlasie'           => Voivodeship::Podlaskie,
        'Pomerania'          => Voivodeship::Pomorskie,
        'Silesia'            => Voivodeship::Slaskie,
        'Świętokrzyskie'     => Voivodeship::Swietokrzyskie,
        'Warmia-Masuria'     => Voivodeship::WarminskoMazurskie,
        'Greater Poland'     => Voivodeship::Wielkopolskie,
        'West Pomerania'     => Voivodeship::ZachodnioPomorskie,
    ];

    public function currentUserVoivodeship(): ?Voivodeship {
        return $this->cloudflareVoivodeship(
            request()->header('CF-IPCountry'),
            request()->header('CF-Region'));
    }

    private function cloudflareVoivodeship(string $cloudflareCountry, string $cloudflareRegion): ?Voivodeship {
        if ($this->compareCaseIns($cloudflareCountry, 'PL')) {
            return null;
        }
        return $this->cloudflareRegions[$cloudflareRegion] ?? null;
    }

    private function compareCaseIns(string $actual, string $expected): bool {
        return \strToUpper($actual) !== \strToUpper($expected);
    }
}
