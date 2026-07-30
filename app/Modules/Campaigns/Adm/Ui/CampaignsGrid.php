<?php
namespace Coyote\Modules\Campaigns\Adm\Ui;

use Boduch\Grid\Components\EditButton;
use Boduch\Grid\Components\ShowButton;
use Boduch\Grid\Order;
use Coyote\Domain\Html;
use Coyote\Domain\Icon\Icons;
use Coyote\Modules\Campaigns\Eloquent\Campaign;
use Coyote\Services\Grid\Grid;
use Modules\Campaigns\CampaignService;

class CampaignsGrid extends Grid {
    public function buildGrid(): void {
        $campaigns = $this->campaignsService();
        $this
            ->setDefaultOrder(new Order('id', 'desc'))
            ->addColumn('id', [
                'title'     => 'ID',
                'clickable' => function (Campaign $row) {
                    return link_to_route('adm.campaigns.show', $row->id, [$row->id]);
                },
            ])
            ->addColumn('name', [
                'title' => 'Nazwa kampanii',
            ])
            ->addColumn('is_premium', [
                'title'  => 'Rodzaj',
                'render' => fn(Campaign $campaign) => $this->campaignPremiumCell($campaign),
            ])
            ->addColumn('is_active', [
                'title'  => 'Aktywna',
                'render' => fn(Campaign $campaign) => $this->campaignActiveCell($campaigns, $campaign),
            ])
            ->addRowAction(new ShowButton(fn(Campaign $row) => route('adm.campaigns.show', [$row->id])))
            ->addRowAction(new EditButton(fn(Campaign $row) => route('adm.campaigns.save', [$row->id])));
    }

    private function campaignsService(): CampaignService {
        return app(CampaignService::class);
    }

    private function campaignActiveCell(CampaignService $campaigns, Campaign $campaign): string {
        $active = $campaigns->campaignStatus($campaign->id) === 'active';
        $icon = $this->icon($active ? 'campaignStatusActive' : 'campaignStatusInactive');
        $title = $active ? 'aktywna' : 'nie aktywna';
        return "$icon $title";
    }

    private function campaignPremiumCell(Campaign $campaign): string {
        if ($campaign->is_premium) {
            $icon = $this->icon('campaignPremium');
            return "$icon Premium";
        }
        return '';
    }

    private function icon(string $iconName): Html {
        return new Icons()->icon($iconName);
    }
}
