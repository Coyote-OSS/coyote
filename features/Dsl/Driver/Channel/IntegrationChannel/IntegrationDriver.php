<?php
namespace Features\Dsl\Driver\Channel\IntegrationChannel;

use Coyote\Modules\Campaigns\Eloquent;
use Features\Dsl\Driver\Driver;
use Illuminate\Contracts\Console;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Application;
use Libs\Arrays\arrays;
use Modules\Campaigns;
use Modules\Campaigns\CampaignBanner;
use Modules\Campaigns\Store\CampaignPayload;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\Store\VariantPayload;
use Modules\Campaigns\VariantType;

class IntegrationDriver implements Driver {
    private readonly AliasRegistry $aliases;
    private readonly Application $app;
    private readonly CampaignsStore $store;
    private readonly Campaigns\ForCampaignBanners $banners;
    private array $campaignIds = [];
    private ?Campaigns\CampaignBannerSet $bannerSet = null;

    public function __construct() {
        $this->aliases = new AliasRegistry();
        $this->app = require __DIR__ . '/../../../../../bootstrap/app.php';
        $this->app->make(Console\Kernel::class)->bootstrap();
        Eloquent\Campaign::query()->delete();
        $this->store = $this->app->make(CampaignsStore::class);
        $this->banners = $this->app->make(Campaigns\ForCampaignBanners::class);
    }

    public function createCampaign(string $campaign, bool $premium): void {
        $this->campaignIds[$campaign] = $this->store->createCampaign(new CampaignPayload(
            name:$campaign,
            redirectUrl:'',
            activeSinceDate:null,
            activeUntilDate:null,
            targetViews:1000,
            description:null,
            isPremium:$premium,
            voivodeship:null));
    }

    public function addVariant(string $campaign, string $variantType, string $variantUrl): void {
        $this->store->createVariant(
            $this->campaignIds[$campaign],
            new VariantPayload($this->variantType($variantType), $variantUrl));
    }

    private function variantType(string $variantType): VariantType {
        return match ($variantType) {
            'banner'         => VariantType::Banner,
            'banner-xl'      => VariantType::BannerXl,
            'leaderboard'    => VariantType::LeaderBoard,
            'leaderboard-xl' => VariantType::LeaderBoardXl,
            'rectangle'      => VariantType::Rectangle,
            'rectangle-xl'   => VariantType::RectangleXl,
            default          => throw new \Exception("Unknown variant type: $variantType")
        };
    }

    public function resolveVariantsForUser(string $deviceType): void {
        if ($deviceType === 'mobile') {
            throw new \Exception('Mobile not yet supported.');
        }
        $this->bannerSet = $this->banners->bannerSet();
    }

    public function variantsForSlot(string $slotType): array {
        if ($slotType === 'square') {
            return [$this->bannerSet->sidebar->imageUrl];
        }
        return $this->bannerSet->horizontal
                |> arrays::map(fn(CampaignBanner $banner) => $banner->imageUrl)
                |> arrays::values();
    }

    public function close(): void {
        $this->database()->disconnect();
    }

    private function database(): DatabaseManager {
        return $this->app->make(DatabaseManager::class);
    }
}
