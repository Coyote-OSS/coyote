<?php
namespace Tests\Integration\Modules\Campaigns;

use Coyote\Modules\Campaigns\CampaignsController;
use Modules\Campaigns\CampaignService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;

#[CoversClass(CampaignsController::class)]
class CampaignsControllerTest extends TestCase {
    use Server\Laravel\Transactional;

    #[Test]
    public function redirectsToHomepage_forNoSuchCampaign(): void {
        $this->laravel
            ->get('/campaigns/no-such-campaign/banner')
            ->assertRedirect('/');
    }

    #[Test]
    public function redirectToCampaignRedirectUrl(): void {
        $this->addCampaign('campaign-key', '/redirect-url');
        $this->laravel
            ->get('/campaigns/campaign-key/banner')
            ->assertRedirect('/redirect-url');
    }

    private function addCampaign(string $campaignKey, string $redirectUrl): void {
        $this->instance()->add(
            '',
            '',
            $campaignKey,
            $redirectUrl,
            null,
            null,
            null);
    }

    private function instance(): CampaignService {
        return $this->laravel->app->get(CampaignService::class);
    }
}
