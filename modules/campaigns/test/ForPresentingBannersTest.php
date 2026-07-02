<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\CampaignBannersPresenter;
use Modules\Campaigns\CampaignService;
use Modules\Campaigns\ForPresentingBanners;
use Modules\Campaigns\Internal\CampaignBanner;
use Modules\Campaigns\Internal\CampaignBanners;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\VariantType;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ForPresentingBanners::class)]
#[CoversClass(CampaignBannersPresenter::class)]
class ForPresentingBannersTest extends TestCase {
    private ForPresentingBanners $presenter;
    private CampaignService|MockObject $campaignService;
    private CampaignsStore|MockObject $campaignStore;

    #[Before]
    public function initialize(): void {
        $this->campaignService = $this->createStub(CampaignService::class);
        $this->campaignStore = $this->createMock(CampaignsStore::class);
        $this->presenter = new CampaignBannersPresenter(
            $this->campaignService,
            $this->campaignStore,
            new TestRedirectUrls("https://test-redirect"));
    }

    #[Test]
    public function noHorizontalBanners(): void {
        // arrange
        $this->stubCampaignBannersEmpty();
        // act
        $bannerSet = $this->presenter->bannerSet();
        // assert
        $this->assertEmpty($bannerSet->horizontal);
    }

    #[Test]
    public function noSidebarBanner(): void {
        // arrange
        $this->stubCampaignBannersEmpty();
        // act
        $bannerSet = $this->presenter->bannerSet();
        // assert
        $this->assertNull($bannerSet->sidebar);
    }

    #[Test]
    public function horizontalBannerImageUrl(): void {
        // arrange
        $this->stubCampaignBanners(new CampaignBanners(
            [$this->banner('img.png', variantId:1)],
            null));
        // act
        $bannerSet = $this->presenter->bannerSet();
        // assert
        $this->assertSame('img.png', $bannerSet->horizontal[0]->imageUrl);
    }

    #[Test]
    public function horizontalBannerRedirectUrl(): void {
        // arrange
        $this->stubCampaignBanners(new CampaignBanners(
            [$this->banner('img.png', variantId:42)],
            null));
        // act
        $bannerSet = $this->presenter->bannerSet();
        // assert
        $this->assertSame('https://test-redirect/42', $bannerSet->horizontal[0]->redirectUrl);
    }

    #[Test]
    public function multipleHorizontalBanners(): void {
        // arrange
        $campaignBanners = new CampaignBanners(
            [$this->banner('one.png', variantId:1), $this->banner('two.png', variantId:2)],
            null);
        $this->stubCampaignBanners($campaignBanners);
        // act
        $bannerSet = $this->presenter->bannerSet();
        // assert
        $this->assertCount(2, $bannerSet->horizontal);
    }

    #[Test]
    public function sidebarBannerImageUrl(): void {
        // arrange
        $this->stubCampaignBanners(new CampaignBanners(
            [],
            $this->banner('side.png', variantId:7, type:VariantType::Sidebar)));
        // act
        $bannerSet = $this->presenter->bannerSet();
        // assert
        $this->assertSame('side.png', $bannerSet->sidebar->imageUrl);
    }

    #[Test]
    public function sidebarBannerRedirectUrl(): void {
        // arrange
        $this->stubCampaignBanners(new CampaignBanners(
            [],
            $this->banner('side.png', variantId:7, type:VariantType::Sidebar)));
        // act
        $bannerSet = $this->presenter->bannerSet();
        // assert
        $this->assertSame('https://test-redirect/7', $bannerSet->sidebar->redirectUrl);
    }

    #[Test]
    public function sidebarBannerExposeUrl(): void {
        // arrange
        $this->stubCampaignBanners(new CampaignBanners(
            [],
            $this->banner('side.png', variantId:7, type:VariantType::Sidebar)));
        // act
        $bannerSet = $this->presenter->bannerSet();
        // assert
        $this->assertSame('https://test-redirect/7/expose', $bannerSet->sidebar->exposeUrl);
    }

    #[Test]
    public function recordsNoViewsForEmptyBannerSet(): void {
        // arrange
        $this->stubCampaignBannersEmpty();
        // assert-expect
        $this->campaignStore
            ->expects($this->never())
            ->method('viewVariant');
        // act
        $this->presenter->recordViews($this->presenter->bannerSet());
    }

    #[Test]
    public function recordsViewForHorizontalBanner(): void {
        // arrange
        $this->stubCampaignBanners(new CampaignBanners(
            [$this->banner('img.png', variantId:42)],
            null));
        // assert-expect
        $this->campaignStore->expects($this->once())->method('viewVariant')->with(42);
        // act
        $this->presenter->recordViews($this->presenter->bannerSet());
    }

    #[Test]
    public function recordsViewForSidebarBanner(): void {
        // arrange
        $this->stubCampaignBanners(new CampaignBanners(
            [],
            $this->banner('side.png', variantId:7, type:VariantType::Sidebar)));
        // assert-expect
        $this->campaignStore->expects($this->once())->method('viewVariant')->with(7);
        // act
        $this->presenter->recordViews($this->presenter->bannerSet());
    }

    #[Test]
    public function recordsViewsForAllBanners(): void {
        // arrange
        $this->stubCampaignBanners(new CampaignBanners(
            [$this->banner('h1.png', variantId:1), $this->banner('h2.png', variantId:2)],
            $this->banner('side.png', variantId:3, type:VariantType::Sidebar)));
        // assert-expect
        $this->campaignStore
            ->expects($this->exactly(3))
            ->method('viewVariant')
            ->withParameterSetsInOrder([1], [2], [3]);
        // act
        $this->presenter->recordViews($this->presenter->bannerSet());
    }

    private function banner(
        string       $bannerUrl,
        int          $variantId,
        ?VariantType $type = null,
    ): CampaignBanner {
        return new CampaignBanner(
            $bannerUrl,
            'irrelevant',
            $type ?? VariantType::Standard,
            $variantId,
        );
    }

    private function stubCampaignBannersEmpty(): void {
        $this->stubCampaignBanners(new CampaignBanners([], null));
    }

    private function stubCampaignBanners(CampaignBanners $campaignBanners): void {
        $this->campaignService->method('campaignBanners')->willReturn($campaignBanners);
    }
}
