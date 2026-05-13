<?php
namespace Tests\Integration\Modules\Campaigns;

use Coyote\Modules\Campaigns\CampaignController;
use Modules\Campaigns\Campaigns;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;

#[CoversClass(CampaignController::class)]
class CampaignControllerTest extends TestCase {
    use BaseFixture\Server\Http;

    #[Test]
    public function redirectsToHomepage_forNoSuchCampaign(): void {
        $this->laravel
            ->get('/campaigns/no-such-campaign')
            ->assertRedirect('/');
    }

    #[Test]
    public function redirectToCampaignRedirectUrl(): void {
        $this->addCampaign('campaign-key', '/redirect-url');
        $this->laravel
            ->get('/campaigns/campaign-key')
            ->assertRedirect('/redirect-url');
    }

    private function addCampaign(string $campaignKey, string $redirectUrl): void {
        $this->instance()->add('', '',
            $campaignKey, $redirectUrl);
    }

    private function instance(): Campaigns {
        return $this->laravel->app->get(Campaigns::class);
    }
}
