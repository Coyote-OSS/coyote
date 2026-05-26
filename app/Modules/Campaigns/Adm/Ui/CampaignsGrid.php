<?php
namespace Coyote\Modules\Campaigns\Adm\Ui;

use Boduch\Grid\Components\EditButton;
use Boduch\Grid\Components\ShowButton;
use Boduch\Grid\Decorators\CodeText;
use Boduch\Grid\Decorators\LongText;
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
                'sortable'  => true,
                'clickable' => function (Campaign $row) {
                    return link_to_route('adm.campaigns.show', $row->id, [$row->id]);
                },
            ])
            ->addColumn('campaign_key', [
                'title'      => 'Klucz',
                'sortable'   => true,
                'decorators' => [new CodeText()],
            ])
            ->addColumn('redirect_url', [
                'title'      => 'URL przekierowania',
                'decorators' => [new LongText()],
            ])
            ->addColumn('is_active', [
                'title'  => 'Aktywna',
                'render' => fn(Campaign $campaign) => $this->campaignActiveCell($campaigns, $campaign),
            ])
            ->addRowAction(new ShowButton(function (Campaign $row) {
                return route('adm.campaigns.show', [$row->id]);
            }))
            ->addRowAction(new EditButton(function (Campaign $row) {
                return route('adm.campaigns.save', [$row->id]);
            }));
    }

    private function campaignsService(): CampaignService {
        return app(CampaignService::class);
    }

    private function campaignActiveCell(CampaignService $campaigns, Campaign $campaign): string {
        $active = $campaigns->isCampaignActive($campaign->campaign_key);
        $icon = $this->icon($active ? 'campaignStatusActive' : 'campaignStatusInactive');
        $title = $active ? 'aktywna' : 'nie aktywna';
        return "$icon $title";
    }

    private function icon(string $iconName): Html {
        return new Icons()->icon($iconName);
    }
}
