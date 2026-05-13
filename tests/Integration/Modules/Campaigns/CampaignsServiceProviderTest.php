<?php
namespace Tests\Integration\Modules\Campaigns;

use Coyote\Modules\Campaigns\AuthPriviligedUsers;
use Coyote\Modules\Campaigns\CampaignsServiceProvider;
use Modules\Campaigns\ForPriviligedUsers;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;

#[CoversClass(CampaignsServiceProvider::class)]
class CampaignsServiceProviderTest extends TestCase {
    use BaseFixture\Server\Http;

    #[Test]
    public function serviceProviderRegisteresAuthPriviligedUsers(): void {
        $users = $this->laravel->app->get(ForPriviligedUsers::class);
        $this->assertInstanceOf(AuthPriviligedUsers::class, $users);
    }
}
