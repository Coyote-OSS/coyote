<?php
namespace Tests\Integration\Modules\Campaigns\User\Ui;

use Modules\Campaigns\New\CampaignBanner;
use Modules\Campaigns\New\CampaignBannerSet;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Integration\Fixture;

class TwigMacroCampaignBannerTest extends TestCase {
    use Fixture\Ui\Twig;

    private string $campaignBanner = "
        {% from 'campaignBanner.campaignBanner' import campaignBanner %}
        {{ campaignBanner(placeholderType, bannerSet) }}
    ";

    #[Test]
    public function rendersEmptyCampaignBannerContainer(): void {
        $bannerSet = new CampaignBannerSet([], null);
        $html = $this->campaignBanner('horizontal', $bannerSet);
        $this->assertNotNull($html->querySelector('.campaign-banner'));
    }

    #[Test]
    public function rendersSidebarBannerLinkAndImage(): void {
        $bannerSet = new CampaignBannerSet([], new CampaignBanner(
            redirectUrl:'https://example.com/sidebar',
            imageUrl:'https://example.com/sidebar.jpg',
        ));
        $html = $this->campaignBanner('sidebar', $bannerSet);
        $this->assertSame('https://example.com/sidebar',
            $html->querySelector('a')->getAttribute('href'));
        $this->assertSame('https://example.com/sidebar.jpg',
            $html->querySelector('img')->getAttribute('src'));
    }

    #[Test]
    public function rendersHorizontalBannerLinkAndImage(): void {
        $banner = new CampaignBanner(
            redirectUrl:'https://example.com/horizontal',
            imageUrl:'https://example.com/horizontal.jpg');
        $bannerSet = new CampaignBannerSet([$banner], null);
        $html = $this->campaignBanner('horizontal', $bannerSet);
        $this->assertSame('https://example.com/horizontal',
            $html->querySelector('a')->getAttribute('href'));
        $this->assertSame('https://example.com/horizontal.jpg',
            $html->querySelector('img')->getAttribute('src'));
    }

    #[Test]
    public function horizontalPlaceholderDoesNotRenderSidebar(): void {
        $sidebar = new CampaignBanner(
            redirectUrl:'https://example.com/sidebar',
            imageUrl:'https://example.com/sidebar.jpg');
        $bannerSet = new CampaignBannerSet([], $sidebar);
        $html = $this->campaignBanner('horizontal', $bannerSet);
        $this->assertNull($html->querySelector('a'));
    }

    #[Test]
    public function sidebarPlaceholderDoesNotRenderHorizontal(): void {
        $banner = new CampaignBanner(
            redirectUrl:'https://example.com/horizontal',
            imageUrl:'https://example.com/horizontal.jpg');
        $bannerSet = new CampaignBannerSet([$banner], null);
        $dom = $this->campaignBanner('sidebar', $bannerSet);
        $this->assertNull($dom->querySelector('a'));
    }

    private function campaignBanner(
        string            $placeholderType,
        CampaignBannerSet $bannerSet,
    ): \Dom\HTMLDocument {
        return $this->renderTwigTemplate($this->campaignBanner, [
            'placeholderType' => $placeholderType,
            'bannerSet'       => $bannerSet,
        ]);
    }
}
