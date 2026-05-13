<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Campaigns;

class CampaignController extends Controller {
    public function __construct(private readonly Campaigns\Campaigns $campaigns) {}

    public function click(string $campaignKey): RedirectResponse {
        return redirect()->to($this->redirectUrl($campaignKey));
    }

    private function redirectUrl(string $campaignKey): string {
        try {
            return $this->campaigns->redirectUrl($campaignKey);
        } catch (Campaigns\NoSuchCampaign) {
            return '/';
        }
    }
}
