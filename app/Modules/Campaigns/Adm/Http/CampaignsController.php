<?php
namespace Coyote\Modules\Campaigns\Adm\Http;

use Boduch\Grid\Source\EloquentSource;
use Coyote\Http\Controllers\Adm\BaseController;
use Coyote\Modules\Campaigns\Adm\Ui\CampaignsForm;
use Coyote\Modules\Campaigns\Adm\Ui\CampaignsGrid;
use Coyote\Modules\Campaigns\Adm\View\CampaignStats;
use Coyote\Modules\Campaigns\Adm\View\CampaignStatus;
use Coyote\Modules\Campaigns\Adm\View\CampaignViewModel;
use Coyote\Modules\Campaigns\Adm\View\VariantViewModel;
use Coyote\Modules\Campaigns\Eloquent;
use Coyote\Modules\Campaigns\Eloquent\Campaign;
use Coyote\Services\FormBuilder\Form;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Libs\Arrays\arrays;
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

    public function show(
        int                            $campaignId,
        Campaigns\CampaignService      $service,
        Campaigns\Store\CampaignsStore $store,
    ): View {
        $campaign = $store->findCampaign($campaignId);
        if ($campaign === null) {
            abort(404);
        }
        return $this->view('adm.campaigns.show', [
            'campaign' => new CampaignViewModel(
                $campaign->payload->name,
                $campaign->payload->redirectUrl,
                route('adm.campaigns.save', [$campaignId]),
                route('adm.campaigns'),
                route('adm.campaigns.variants.save', [$campaignId]),
                $this->campaignStats($campaign),
                new CampaignStatus($service->campaignStatus($campaignId)),
                $campaign->payload->activeSinceDate,
                $campaign->payload->activeUntilDate,
                $campaign->payload->activeBelowViews,
                $campaign->variants |> arrays::map(
                    fn(Campaigns\Store\CampaignVariant $variant) => new VariantViewModel(
                        $variant->payload->imageUrl,
                        new CampaignStats($variant->views, $variant->clicks),
                        $variant->payload->type))),
        ]);
    }

    private function campaignStats(Campaigns\Store\Campaign $campaign): CampaignStats {
        $views = 0;
        $clicks = 0;
        foreach ($campaign->variants as $variant) {
            $views += $variant->views;
            $clicks += $variant->clicks;
        }
        return new CampaignStats($views, $clicks);
    }

    public function edit(Eloquent\Campaign $campaign): View {
        $this->breadcrumb->push('Edycja', route('adm.campaigns.save', ['campaign' => $campaign]));
        return $this->view('adm.campaigns.save')->with('form', $this->getForm($campaign));
    }

    public function save(Eloquent\Campaign $campaign, Campaigns\Store\CampaignsStore $store): RedirectResponse {
        $form = $this->getForm($campaign);
        $form->validate();
        $campaignModel = new Campaigns\Store\CampaignPayload(
            $form->getValue('name'),
            $form->getValue('redirect_url'),
            $form->getValue('active_since'),
            $form->getValue('active_until'),
            $form->getValue('target_views'),
            null);
        if ($campaign->exists) {
            $store->updateCampaign($campaign->id, $campaignModel);
            $campaignId = $campaign->id;
        } else {
            $campaignId = $store->createCampaign($campaignModel);
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
