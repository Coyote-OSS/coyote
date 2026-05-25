<?php
namespace Coyote\Modules\Campaigns\Adm;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Controllers\Adm\BaseController;
use Coyote\Modules\Campaigns\Adm\View\BannerViewModel;
use Coyote\Modules\Campaigns\Adm\View\CampaignStats;
use Coyote\Modules\Campaigns\Adm\View\CampaignViewModel;
use Coyote\Modules\Campaigns\Eloquent\Campaign;
use Coyote\Services\FormBuilder\Form;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Campaigns\CampaignsStore;

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

    public function show(Campaign $campaign, CampaignsStore $store): View {
        $campaignKey = $campaign->campaign_key;

        $horizontalStats = new CampaignStats(
            $store->campaignViewCount($campaignKey, 'horizontal'),
            $store->campaignClickCount($campaignKey, 'horizontal'));

        $sidebarStats = new CampaignStats(
            $store->campaignViewCount($campaignKey, 'sidebar'),
            $store->campaignClickCount($campaignKey, 'sidebar'));

        return $this->view('adm.campaigns.show', [
            'campaign'         => new CampaignViewModel(
                $campaignKey,
                $campaign->redirect_url,
                route('adm.campaigns.save', [$campaign->id]),
                $horizontalStats->concat($sidebarStats)),
            'bannerHorizontal' => new BannerViewModel($campaign->horizontal, $horizontalStats),
            'bannerSidebar'    => new BannerViewModel($campaign->sidebar, $sidebarStats),
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
