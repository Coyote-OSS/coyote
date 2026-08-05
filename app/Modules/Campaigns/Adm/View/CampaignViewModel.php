<?php
namespace Coyote\Modules\Campaigns\Adm\View;

use Coyote\Modules\Campaigns\Adm\VoivodeshipLabel;
use Modules\Campaigns\Voivodeship;

readonly class CampaignViewModel {
    private VoivodeshipLabel $voivodeship;

    /**
     * @param VariantViewModel[] $variants
     */
    public function __construct(
        public ?string        $name,
        public bool           $isPremium,
        public ?string        $description,
        public string         $redirectUrl,
        public string         $editHref,
        public string         $backHref,
        public string         $uploadVariantsHref,
        public CampaignStats  $stats,
        public CampaignStatus $status,
        public ?string        $dateSince,
        public ?string        $dateUntil,
        public ?int           $targetViews,
        ?Voivodeship          $voivodeship,
        public array          $variants,
    ) {
        $this->voivodeship = new VoivodeshipLabel($voivodeship);
    }

    public function voivodeship(): string {
        return $this->voivodeship->label();
    }
}
