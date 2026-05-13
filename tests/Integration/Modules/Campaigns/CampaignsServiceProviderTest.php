<?php
namespace Tests\Integration\Modules\Campaigns;

use Coyote\Modules\Campaigns\AuthPriviligedUsers;
use Coyote\Modules\Campaigns\CampaignsServiceProvider;
use Coyote\Modules\Campaigns\TimeRotatingBanners;
use Modules\Campaigns;
use Modules\Campaigns\ForPriviligedUsers;
use Modules\Campaigns\ForRotatingBanners;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;

#[CoversClass(CampaignsServiceProvider::class)]
class CampaignsServiceProviderTest extends TestCase {
    use BaseFixture\Server\Http;

    #[Test]
    public function serviceProviderRegistersAuthPriviligedUsers(): void {
        $users = $this->laravel->app->get(ForPriviligedUsers::class);
        $this->assertInstanceOf(AuthPriviligedUsers::class, $users);
    }

    #[Test]
    public function serviceProviderRegistersTimeRotatingBanners(): void {
        $rotating = $this->laravel->app->get(ForRotatingBanners::class);
        $this->assertInstanceOf(TimeRotatingBanners::class, $rotating);
    }

    #[Test]
    public function hardcodedCampaignBanners(): void {
        /** @var Campaigns\Campaigns $campaigns */
        $campaigns = $this->laravel->app->get(Campaigns\Campaigns::class);
        $banners = $campaigns->campaignBanners();
        $this->assertContainsEquals($banners->sidebar, [
            '/img/jobAd/mobileViking/narrow-h250.png',
            '/img/jobAd/myDevil/narrow-h250.png',
        ]);
        $this->assertEquals([
            '/img/jobAd/mobileViking/wide-h200.png',
            '/img/jobAd/myDevil/wide-h200.png',
        ], $banners->horizontal);
    }
}
