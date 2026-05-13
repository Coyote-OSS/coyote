<?php
namespace Coyote\Modules\Campaigns\Adm;

use Boduch\Grid\Components\EditButton;
use Boduch\Grid\Decorators\LongText;
use Boduch\Grid\Order;
use Coyote\Modules\Campaigns\Eloquent\Campaign;
use Coyote\Services\Grid\Grid;

class CampaignsGrid extends Grid {
    public function buildGrid(): void {
        $this
            ->setDefaultOrder(new Order('id', 'asc'))
            ->addColumn('id', [
                'title'     => 'ID',
                'sortable'  => true,
                'clickable' => function (Campaign $row) {
                    return link_to_route('adm.campaigns.save', $row->id, [$row->id]);
                },
            ])
            ->addColumn('campaign_key', [
                'title'     => 'Klucz',
                'sortable'  => true,
                'clickable' => function (Campaign $row) {
                    return link_to_route('adm.campaigns.save', $row->campaign_key, [$row->id]);
                },
            ])
            ->addColumn('redirect_url', [
                'title'      => 'URL przekierowania',
                'decorators' => [new LongText()],
            ])
            ->addRowAction(new EditButton(function (Campaign $row) {
                return route('adm.campaigns.save', [$row->id]);
            }));
    }
}
