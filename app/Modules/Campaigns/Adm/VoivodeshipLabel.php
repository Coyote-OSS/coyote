<?php
namespace Coyote\Modules\Campaigns\Adm;

use Modules\Campaigns\Voivodeship;

readonly class VoivodeshipLabel {
    public function __construct(private ?Voivodeship $voivodeship) {}

    public function label(): string {
        return match ($this->voivodeship) {
            null                            => 'każde województwo',
            Voivodeship::Dolnoslaskie       => 'Polska - Dolnośląskie',
            Voivodeship::KujawskoPomorskie  => 'Polska - Kujawsko-Pomorskie',
            Voivodeship::Lubelskie          => 'Polska - Lubelskie',
            Voivodeship::Lubuskie           => 'Polska - Lubuskie',
            Voivodeship::Lodzkie            => 'Polska - Łódzkie',
            Voivodeship::Malopolskie        => 'Polska - Małopolskie',
            Voivodeship::Mazowieckie        => 'Polska - Mazowieckie',
            Voivodeship::Opolskie           => 'Polska - Opolskie',
            Voivodeship::Podkarpackie       => 'Polska - Podkarpackie',
            Voivodeship::Podlaskie          => 'Polska - Podlaskie',
            Voivodeship::Pomorskie          => 'Polska - Pomorskie',
            Voivodeship::Slaskie            => 'Polska - Śląskie',
            Voivodeship::Swietokrzyskie     => 'Polska - Świętokrzyskie',
            Voivodeship::WarminskoMazurskie => 'Polska - Warmińsko-Mazurskie',
            Voivodeship::Wielkopolskie      => 'Polska - Wielkopolskie',
            Voivodeship::ZachodnioPomorskie => 'Polska - Zachodniopomorskie',
        };
    }
}
