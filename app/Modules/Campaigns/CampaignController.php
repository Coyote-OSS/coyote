<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Campaigns;

class CampaignController extends Controller {
    public function __construct(private readonly Campaigns\Campaigns $campaigns) {}

    public function click(
        Request $request,
        string  $campaignKey,
    ): RedirectResponse {
        try {
            return redirect()
                ->to($this->campaigns->redirectUrl($campaignKey));
        } catch (Campaigns\NoSuchCampaign) {
            return redirect()->to('/');
        }
    }
}
