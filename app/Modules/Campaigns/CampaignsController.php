<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Database\Connection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Campaigns;

class CampaignsController extends Controller {
    public function __construct(private readonly Campaigns\Campaigns $campaigns) {}

    public function click(string $campaignKey): RedirectResponse {
        $this->incrementClick($campaignKey);
        return redirect()->to($this->redirectUrl($campaignKey));
    }

    private function redirectUrl(string $campaignKey): string {
        try {
            return $this->campaigns->redirectUrl($campaignKey);
        } catch (Campaigns\NoSuchCampaign) {
            return '/';
        }
    }

    private function incrementClick(string $campaignKey): void {
        if ($campaignKey === 'mobileViking') {
            $this->settingsIncrement('job_ad.1.mobile_vikings');
        } else if ($campaignKey === 'myDevil') {
            $this->settingsIncrement('job_ad.1.my_devil');
        } else {
            $this->settingsIncrement("job_ad.1.$campaignKey");
        }
    }

    private function settingsIncrement(string $key): void {
        $connection = app(Connection::class);
        $connection->table('settings_key_value')->insert([
            'key'   => $key,
            'value' => now(),
        ]);
    }
}
