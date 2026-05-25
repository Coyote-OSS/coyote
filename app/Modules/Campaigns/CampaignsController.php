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
        return redirect()->to($this->clickAndRedirect($campaignKey, $bannerType));
    }

    private function clickAndRedirect(string $campaignKey, string $bannerType): string {
        try {
            $this->store->campaignClick($campaignKey, $bannerType);
        } catch (Campaigns\NoSuchCampaign) {
            return '/';
        }
        return $this->campaigns->redirectUrl($campaignKey);
    }
}
