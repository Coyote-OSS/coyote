<?php
namespace Coyote\Modules\Campaigns\Adm\Http;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Controllers\Adm\BaseController;
use Coyote\Modules\Campaigns\Adm\Ui\CampaignsForm;
use Coyote\Modules\Campaigns\Adm\Ui\CampaignsGrid;
use Coyote\Modules\Campaigns\Adm\View\CampaignPresenter;
use Coyote\Modules\Campaigns\Adm\View\CampaignViewModel;
use Coyote\Modules\Campaigns\Eloquent\Campaign;
use Coyote\Services\FormBuilder\Form;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Campaigns;

class CampaignsController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->breadcrumb->push('Kampanie', route('adm.campaigns'));
    }

    public function index(): View {
        return $this->view('adm.campaigns.home', [
            'createFormHref' => route('adm.campaigns.save'),
            'grid'           => $this->gridBuilder()
                ->createGrid(CampaignsGrid::class)
                ->setSource(new EloquentSource(Campaign::query())),
        ]);
    }

    public function show(Campaign $campaign, CampaignPresenter $presenter): View {
        return $this->view('adm.campaigns.show', [
            'campaign' => new CampaignViewModel(
                $campaign->campaign_key,
                $campaign->redirect_url,
                route('adm.campaigns.save', [$campaign->id]),
                route('adm.campaigns'),
                route('adm.campaigns.variants.save', [$campaign->id]),
                $presenter->campaignStats($campaign->campaign_key),
                $presenter->campaignStatus($campaign->campaign_key),
                $campaign->active_since,
                $campaign->active_until,
                $campaign->target_views,
                $presenter->bannerViewModelsById($campaign->id)),
        ]);
    }

    public function edit(Campaign $campaign): View {
        $this->breadcrumb->push('Edycja', route('adm.campaigns.save', ['campaign' => $campaign]));
        return $this->view('adm.campaigns.save')->with('form', $this->getForm($campaign));
    }

    public function save(Campaign $campaign, Campaigns\CampaignsStore $store): RedirectResponse {
        $form = $this->getForm($campaign);
        $form->validate();
        $campaignModel = Campaigns\Campaign::create(
            $form->getValue('campaign_key'),
            $form->getValue('sidebar') ?? 'not-to-be-used-deprecated',
            $form->getValue('horizontal') ?? 'not-to-be-used-deprecated',
            $form->getValue('redirect_url'),
            $form->getValue('active_since'),
            $form->getValue('active_until'),
            $form->getValue('target_views'));
        if ($campaign->exists) {
            $store->updateCampaign($campaign->id, $campaignModel);
            $campaignId = $campaign->id;
        } else {
            $campaignId = $store->createCampaignReturnId($campaignModel);
            if ($campaignId === null) {
                abort(400);
            }
        }
        return redirect()
            ->route('adm.campaigns.show', [$campaignId])
            ->header('X-Campaign-Id', $campaignId)
            ->with('success', 'Zmiany zostały zapisane.');
    }

    public function delete(Campaign $campaign): RedirectResponse {
        $campaign->delete();
        return redirect()->route('adm.campaigns')->with('success', 'Kampania została usunięta.');
    }

    private function getForm(Campaign $campaign): Form {
        return $this->createForm(CampaignsForm::class, $campaign);
    }
}
