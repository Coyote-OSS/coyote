<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Campaigns;

class CampaignsController extends Controller {
    public function __construct(private readonly Campaigns\Store\CampaignsStore $store) {}

    public function click(int $variantId): RedirectResponse {
        $redirectUrl = $this->store->findCampaignRedirectUrl($variantId);
        if ($redirectUrl === null) {
            abort(404);
        }
        $this->store->clickVariant($variantId);
        return redirect()->to($redirectUrl);
    }
}
