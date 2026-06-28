<?php
namespace Tests\Integration\Modules\Campaigns\User\Ui;

use Modules\Campaigns\CampaignBanner;
use Modules\Campaigns\CampaignBanners;
use Modules\Campaigns\VariantType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Integration\Fixture;

class TwigMacroCampaignBannerTest extends TestCase {
    use Fixture\Ui\Twig;

    private string $campaignBanner = "
        {% from 'jobAdPlaceholder.jobAdPlaceholder' import jobAdPlaceholder %}
        {{ jobAdPlaceholder(banners, size) }}
    ";

    #[Test]
    public function rendersEmptyCampaignBannerContainer(): void {
        $banners = new CampaignBanners([], null);
        $html = $this->jobAdPlaceholder('horizontal', $banners);
        $this->assertNull($html->querySelector('.job-ad-placeholder'));
    }

    #[Test]
    public function rendersSidebarBannerLinkAndImage(): void {
        $banners = new CampaignBanners([], new CampaignBanner(
            bannerUrl:'https://example.com/sidebar.jpg',
            campaignKey:'',
            type:VariantType::Sidebar,
            variantId:12));
        $html = $this->jobAdPlaceholder('sidebar', $banners);
        $this->assertSame('/campaigns/12',
            $html->querySelector('a')->getAttribute('href'));
        $this->assertSame('https://example.com/sidebar.jpg',
            $html->querySelector('img')->getAttribute('src'));
    }

    #[Test]
    public function rendersHorizontalBannerLinkAndImage(): void {
        $campaignBanner = new CampaignBanner(
            bannerUrl:'https://example.com/horizontal.jpg',
            campaignKey:'',
            type:VariantType::Standard,
            variantId:13);
        $banners = new CampaignBanners([$campaignBanner], null);
        $html = $this->jobAdPlaceholder('horizontal', $banners);
        $this->assertSame('/campaigns/13',
            $html->querySelector('a')->getAttribute('href'));
        $this->assertSame('https://example.com/horizontal.jpg',
            $html->querySelector('img')->getAttribute('src'));
    }

    #[Test]
    public function horizontalPlaceholderDoesNotRenderSidebar(): void {
        $sidebar = new CampaignBanner(
            bannerUrl:'https://example.com/sidebar.jpg',
            campaignKey:'',
            type:VariantType::Sidebar,
            variantId:12);
        $banners = new CampaignBanners([], $sidebar);
        $html = $this->jobAdPlaceholder('horizontal', $banners);
        $this->assertNull($html->querySelector('a'));
    }

    #[Test]
    public function sidebarPlaceholderDoesNotRenderHorizontal(): void {
        $bnanner = new CampaignBanner(
            bannerUrl:'https://example.com/horizontal.jpg',
            campaignKey:'',
            type:VariantType::Standard,
            variantId:12);
        $banners = new CampaignBanners([$bnanner], null);
        $dom = $this->jobAdPlaceholder('sidebar', $banners);
        $this->assertNull($dom->querySelector('a'));
    }

    private function jobAdPlaceholder(
        string          $size,
        CampaignBanners $banners,
    ): \Dom\HTMLDocument {
        return $this->renderTwigTemplate($this->campaignBanner, [
            'banners' => $banners,
            'size'    => $size,
        ]);
    }
}
