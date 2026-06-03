<?php
namespace Tests;

use Modules\Campaigns\Campaign;
use Modules\Campaigns\CampaignVariant;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CampaignTest extends TestCase {
    #[Test]
    public function createWithBanners(): void {
        $campaign = Campaign::create(
            '',
            'sidebar-url',
            'horizontal-url',
            '',
            null,
            null,
            null);
        $this->assertSame('sidebar-url', $campaign->sidebarBanner());
        $this->assertSame('horizontal-url', $campaign->horizontalBanner());
    }

    #[Test]
    public function mapSingularBannersToVariants(): void {
        // when
        $campaign = Campaign::create(
            '',
            'sidebar-url',
            'horizontal-url',
            '',
            null,
            null,
            null);
        // then
        [$sidebar, $horizontal] = $campaign->variants;
        $this->assertSame('sidebar-url', $sidebar->bannerUrl);
        $this->assertSame('sidebar', $sidebar->bannerType);
        $this->assertSame('horizontal-url', $horizontal->bannerUrl);
        $this->assertSame('horizontal', $horizontal->bannerType);
    }

    #[Test]
    public function mapVariantsToSingularBanners(): void {
        // when
        $campaign = new Campaign('', '', null, null, null, [
            new CampaignVariant('sidebar-url', 'sidebar'),
            new CampaignVariant('horizontal-url', 'horizontal'),
        ]);
        // then
        $this->assertSame('sidebar-url', $campaign->sidebarBanner());
        $this->assertSame('horizontal-url', $campaign->horizontalBanner());
    }
}
