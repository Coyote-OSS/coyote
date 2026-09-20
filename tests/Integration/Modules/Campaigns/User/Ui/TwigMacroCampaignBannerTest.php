<?php
namespace Tests\Integration\Modules\Campaigns\User\Ui;

use Modules\Campaigns\CampaignBanner;
use Modules\Campaigns\CampaignBannerSet;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Integration\Fixture;

class TwigMacroCampaignBannerTest extends TestCase {
    use Fixture\Ui\Twig;

    private string $campaignBanner = "
        {% from 'campaignBanner.campaignBanner' import campaignBanner %}
        {{ campaignBanner(slotType, desktopBannerSet, mobileBannerSet) }}
    ";

    #[Test]
    public function rendersEmptyCampaignBannerContainer(): void {
        $bannerSet = new CampaignBannerSet([], null, []);
        $html = $this->campaignBanner('header', $bannerSet, $bannerSet);
        $this->assertNull($html->querySelector('.campaign-banner'));
    }

    #[Test]
    public function rendersSquareBannerLinkAndImage(): void {
        $bannerSet = new CampaignBannerSet([], $this->banner(
            'https://example.com/square',
            'https://example.com/square.jpg'), []);
        $html = $this->campaignBanner('square', $bannerSet, $bannerSet);
        $this->assertSame('https://example.com/square',
            $html->querySelector('a')->getAttribute('href'));
        $this->assertSame('https://example.com/square.jpg',
            $html->querySelector('img')->getAttribute('src'));
    }

    #[Test]
    public function rendersSquareBannerExposeUrlDataAttribute(): void {
        $bannerSet = new CampaignBannerSet([], $this->banner(
            '',
            '',
            exposeUrl:'https://example.com/square/expose'), []);
        $html = $this->campaignBanner('square', $bannerSet, $bannerSet);
        $this->assertSame('https://example.com/square/expose',
            $html->querySelector('img')->getAttribute('data-expose-url'));
    }

    #[Test]
    public function rendersSquareBannerAdblockUrlDataAttribute(): void {
        $bannerSet = new CampaignBannerSet([], $this->banner(
            '',
            '',
            adblockUrl:'https://example.com/square/adblock'), []);
        $html = $this->campaignBanner('square', $bannerSet, $bannerSet);
        $this->assertSame('https://example.com/square/adblock',
            $html->querySelector('img')->getAttribute('data-adblock-url'));
    }

    #[Test]
    public function rendersHeaderBannerLinkAndImage(): void {
        $banner = $this->banner(
            redirectUrl:'https://example.com/header',
            imageUrl:'https://example.com/header.jpg');
        $bannerSet = new CampaignBannerSet([$banner], null, []);
        $html = $this->campaignBanner('header', $bannerSet, $bannerSet);
        $this->assertSame('https://example.com/header',
            $html->querySelector('a')->getAttribute('href'));
        $this->assertSame('https://example.com/header.jpg',
            $html->querySelector('img')->getAttribute('src'));
    }

    #[Test]
    public function rendersFeedBannerLinkAndImage(): void {
        $banner = $this->banner(
            redirectUrl:'https://example.com/feed',
            imageUrl:'https://example.com/feed.jpg');
        $bannerSet = new CampaignBannerSet([], null, [$banner]);
        $html = $this->campaignBanner('feed', $bannerSet, $bannerSet);
        $this->assertSame('https://example.com/feed',
            $html->querySelector('a')->getAttribute('href'));
        $this->assertSame('https://example.com/feed.jpg',
            $html->querySelector('img')->getAttribute('src'));
    }

    #[Test]
    public function headerPlaceholderDoesNotRenderSquare(): void {
        $bannerSet = new CampaignBannerSet([], $this->banner(), []);
        $html = $this->campaignBanner('header', $bannerSet, $bannerSet);
        $this->assertNull($html->querySelector('a'));
    }

    #[Test]
    public function squarePlaceholderDoesNotRenderHeader(): void {
        $bannerSet = new CampaignBannerSet([$this->banner()], null, []);
        $dom = $this->campaignBanner('square', $bannerSet, $bannerSet);
        $this->assertNull($dom->querySelector('a'));
    }

    #[Test]
    public function headerPlaceholderDoesNotRenderFeed(): void {
        $bannerSet = new CampaignBannerSet([], null, [$this->banner()]);
        $html = $this->campaignBanner('header', $bannerSet, $bannerSet);
        $this->assertNull($html->querySelector('a'));
    }

    #[Test]
    public function feedPlaceholderDoesNotRenderHeader(): void {
        $bannerSet = new CampaignBannerSet([$this->banner()], null, []);
        $html = $this->campaignBanner('feed', $bannerSet, $bannerSet);
        $this->assertNull($html->querySelector('a'));
    }

    #[Test]
    public function rendersTheMobileBannerSetEvenWhenTheDesktopOneIsEmpty(): void {
        $empty = new CampaignBannerSet([], null, []);
        $mobile = new CampaignBannerSet([], $this->banner(
            'https://example.com/mobile-square',
            'https://example.com/mobile-square.jpg'), []);
        $html = $this->campaignBanner('square', $empty, $mobile);
        $this->assertSame('https://example.com/mobile-square',
            $html->querySelector('a')->getAttribute('href'));
    }

    private function campaignBanner(
        string            $slotType,
        CampaignBannerSet $desktopBannerSet,
        CampaignBannerSet $mobileBannerSet,
    ): \Dom\HTMLDocument {
        return $this->renderTwigTemplate($this->campaignBanner, [
            'slotType'          => $slotType,
            'desktopBannerSet'  => $desktopBannerSet,
            'mobileBannerSet'   => $mobileBannerSet,
        ]);
    }

    private function banner(
        ?string $redirectUrl = null,
        ?string $imageUrl = null,
        ?string $exposeUrl = null,
        ?string $adblockUrl = null,
    ): CampaignBanner {
        return new CampaignBanner(
            redirectUrl:$redirectUrl ?? '',
            exposeUrl:$exposeUrl ?? '',
            adblockUrl:$adblockUrl ?? '',
            imageUrl:$imageUrl ?? '',
            variantId:0);
    }
}
