<?php
namespace Features\Dsl\Driver\Channel\InMemoryChannel;

use Features\Dsl\Driver\Driver;
use Libs\Arrays\arrays;
use Modules\Campaigns\CampaignBanner;
use Modules\Campaigns\CampaignBannerSet;
use Modules\Campaigns\CampaignBannersFacade;
use Modules\Campaigns\CampaignService;
use Modules\Campaigns\DeviceType;
use Modules\Campaigns\ForCampaignBanners;
use Modules\Campaigns\Store\CampaignPayload;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\Store\VariantPayload;
use Modules\Campaigns\VariantType;
use Modules\JobBoard\JobBoardStore;
use Test\Modules\Campaigns\Fixture\TestCurrentDate;
use Test\Modules\Campaigns\Fixture\TestPrivilegedUsers;
use Test\Modules\Campaigns\Fixture\TestRedirectUrls;
use Test\Modules\Campaigns\Fixture\TestRotatingBanners;
use Test\Modules\Campaigns\Fixture\TestUserVoivodeship;
use Test\Modules\Campaigns\Store\InMemoryCampaignsStore;
use Test\Modules\JobBoard\Store\InMemoryJobBoardStore;

class InMemoryDriver implements Driver {
    public static function create(): self {
        $rotatingBanners = new TestRotatingBanners();
        $store = new InMemoryCampaignsStore();
        $facade = new CampaignBannersFacade(
            new CampaignService(
                new TestPrivilegedUsers(),
                $rotatingBanners,
                new TestCurrentDate(),
                $store,
                new TestUserVoivodeship()),
            new TestRedirectUrls('https://example.test'));
        return new InMemoryDriver(
            $store,
            new InMemoryJobBoardStore(),
            $facade,
            $rotatingBanners);
    }

    private array $campaignIds = [];
    private array $jobOfferIds = [];
    private ?CampaignBannerSet $resolvedBanners;

    public function __construct(
        private readonly CampaignsStore      $campaigns,
        private readonly JobBoardStore       $jobBoard,
        private readonly ForCampaignBanners  $service,
        private readonly TestRotatingBanners $rotatingBanners,
    ) {}

    public function createCampaign(string $campaign, bool $premium): void {
        $this->campaignIds[$campaign] = $this->campaigns->createCampaign(new CampaignPayload(
            $campaign, '', null, null, 999, null, $premium, null,
        ));
    }

    public function addVariant(string $campaign, string $variantType, string $variantUrl): void {
        $this->campaigns->createVariant($this->campaignIds[$campaign], new VariantPayload(
            $this->parseVariantType($variantType),
            $variantUrl,
        ));
    }

    public function resolveVariantsForUser(string $deviceType): void {
        $this->resolvedBanners = $this->service->bannerSet($this->deviceType($deviceType));
        $this->rotatingBanners->rotate();
    }

    public function variantsForSlot(string $slotType): array {
        return match ($slotType) {
            'square' => $this->resolvedBanners->sidebar === null
                ? []
                : [$this->resolvedBanners->sidebar->imageUrl],
            'feed'   => $this->resolvedBanners->feed |> arrays::map(fn(CampaignBanner $banner) => $banner->imageUrl),
            'header' => $this->resolvedBanners->horizontal |> arrays::map(fn(CampaignBanner $banner) => $banner->imageUrl),
            default  => throw new \Exception()
        };
    }

    private function deviceType(string $deviceType): DeviceType {
        return match ($deviceType) {
            'desktop' => DeviceType::Desktop,
            'mobile'  => DeviceType::Mobile,
            default   => throw new \Exception()
        };
    }

    private function parseVariantType(string $variantType): VariantType {
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

    public function createJobOffer(string $jobOffer): void {
        $this->jobOfferIds[$jobOffer] = $this->jobBoard->createJobOffer($jobOffer);
    }

    public function clickJobOffer(string $jobOffer): void {
        $this->jobBoard->clickJobOffer($this->jobOfferIds[$jobOffer]);
    }

    public function jobOfferClicks(string $jobOffer): int {
        return $this->jobBoard->jobOfferClicks($this->jobOfferIds[$jobOffer]);
    }

    public function exposeJobOffer(string $jobOffer): void {
        $this->jobBoard->exposeJobOffer($this->jobOfferIds[$jobOffer]);
    }

    public function jobOfferExposures(string $jobOffer): int {
        return $this->jobBoard->jobOfferExposures($this->jobOfferIds[$jobOffer]);
    }

    public function initialize(string $feature, string $scenario): void {}

    public function finalize(): void {}

    public function captureDiagnostics(string $testTitle) {}
}
