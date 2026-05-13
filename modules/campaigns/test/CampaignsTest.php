<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\Campaigns;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Campaigns::class)]
class CampaignsTest extends TestCase {
    private Campaigns $campaigns;
    private TestPriviligedUsers $priviligedUsers;

    #[Before]
    public function initialize(): void {
        $this->priviligedUsers = new TestPriviligedUsers();
        $this->campaigns = new Campaigns($this->priviligedUsers);
    }

    #[Test]
    public function noSidebarBanner(): void {
        $this->assertNull($this->sidebarBanner());
    }

    #[Test]
    public function noHorizontalBanners(): void {
        $this->assertEmpty($this->horizontalBanners());
    }

    #[Test]
    public function singleSidebarBanner(): void {
        $this->campaigns->add('sidebar.png', '');
        $this->assertEquals('sidebar.png', $this->sidebarBanner());
    }

    #[Test]
    public function singleHorizontalBanner(): void {
        $this->campaigns->add('sidebar.png', 'horizontal.png');
        $this->assertEquals(['horizontal.png'], $this->horizontalBanners());
    }

    #[Test]
    public function noBanner_forPriviligedUser_dueToHighReputation(): void {
        $this->campaigns->add('sidebar.png', 'horizontal.png');
        $this->priviligedUsers->setUserHighReputation(true);
        $this->assertNull($this->sidebarBanner());
        $this->assertEmpty($this->horizontalBanners());
    }

    #[Test]
    public function noBanner_forPriviligedUser_dueToBeingSponsor(): void {
        $this->campaigns->add('sidebar.png', 'horizontal.png');
        $this->priviligedUsers->setUserSponsor(true);
        $this->assertNull($this->sidebarBanner());
        $this->assertEmpty($this->horizontalBanners());
    }

    /**
     * @return string[]
     */
    private function horizontalBanners(): array {
        return $this->campaigns->campaignBanners()->horizontal;
    }

    private function sidebarBanner(): ?string {
        return $this->campaigns->campaignBanners()->sidebar;
    }

    #[Test]
    public function twoHorizontalBanners(): void {
        $this->campaigns->add('', 'foo.png');
        $this->campaigns->add('', 'bar.png');
        $this->assertEquals(['foo.png', 'bar.png'], $this->horizontalBanners());
    }

    // TODO two campaigns displayed in horizontal
    // TODO two campaigns displayed in sidebar
    // TODO hide after time passes
    // TODO hide after views passed
    // TODO count and present views
    // TODO count and present clicks
    // TODO present ctr
}
