<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Campaigns;

class CampaignsController extends Controller {
    public function __construct(
        private readonly Campaigns\Campaigns      $campaigns,
        private readonly Campaigns\CampaignsStore $store,
    ) {}

    public function click(string $campaignKey, string $bannerType): RedirectResponse {
        $this->store->campaignClick($campaignKey, $bannerType);
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
