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
            'campaign'         => new CampaignViewModel(
                $campaign->campaign_key,
                $campaign->redirect_url,
                route('adm.campaigns.save', [$campaign->id]),
                $presenter->campaignStats($campaign->campaign_key),
                $presenter->campaignActive($campaign->campaign_key),
                $campaign->active_since,
                $campaign->active_until),
            'bannerHorizontal' => $presenter->horizontalViewModel($campaign->campaign_key, $campaign->horizontal),
            'bannerSidebar'    => $presenter->sidebarViewModel($campaign->campaign_key, $campaign->sidebar),
        ]);
    }

    public function edit(Campaign $campaign): View {
        $this->breadcrumb->push('Edycja', route('adm.campaigns.save', ['campaign' => $campaign]));
        return $this->view('adm.campaigns.save')->with('form', $this->getForm($campaign));
    }

    public function save(Campaign $campaign): RedirectResponse {
        $form = $this->getForm($campaign);
        $form->validate();
        $campaign->fill($form->all());
        $campaign->save();
        return redirect()->route('adm.campaigns')->with('success', 'Zmiany zostały zapisane.');
    }

    public function delete(Campaign $campaign): RedirectResponse {
        $campaign->delete();
        return redirect()->route('adm.campaigns')->with('success', 'Kampania została usunięta.');
    }

    private function getForm(Campaign $campaign): Form {
        return $this->createForm(CampaignsForm::class, $campaign);
    }
}
