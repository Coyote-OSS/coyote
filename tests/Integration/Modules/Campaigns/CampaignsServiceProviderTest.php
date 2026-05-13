<?php
namespace Tests\Integration\Modules\Campaigns;

use Coyote\Modules\Campaigns\AuthPriviligedUsers;
use Coyote\Modules\Campaigns\CampaignsServiceProvider;
use Coyote\Modules\Campaigns\TimeRotatingBanners;
use Modules\Campaigns\ForPriviligedUsers;
use Modules\Campaigns\ForRotatingBanners;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;

#[CoversClass(CampaignsServiceProvider::class)]
class CampaignsServiceProviderTest extends TestCase {
    use Server\Laravel\Transactional;

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
}
