<?php
namespace Coyote\Modules\Campaigns\Adm\Http;

use Coyote\Http\Controllers\Adm\BaseController;
use Coyote\Modules\Campaigns\Adm\Ui\VariantsForm;
use Coyote\Modules\Campaigns\Eloquent\Campaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Campaigns\CampaignsStore;

class VariantsController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->breadcrumb->push('Kampanie', route('adm.campaigns'));
        $this->breadcrumb->push('Warianty', route('adm.campaigns'));
    }

    public function edit(Campaign $campaign): View {
        return $this->view('adm.campaigns.variants.save', [
            'campaignKey' => $campaign->campaign_key,
            'form'        => $this->createForm(VariantsForm::class, $campaign),
        ]);
    }

    public function save(int $campaign, CampaignsStore $store): RedirectResponse {
        $form = $this->createForm(VariantsForm::class);
        $form->validate();
        $created = $store->createVariant(
            $campaign,
            $form->getField('image_url'),
            $form->getField('type'));
        if ($created) {
            return redirect()
                ->route('adm.campaigns.show', [$campaign])
                ->with('success', 'Wariant kampanii został dodany.');
        } else {
            abort(422);
        }
    }
}
