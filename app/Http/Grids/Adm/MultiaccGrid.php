<?php

namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Order;
use Coyote\Models\Multiacc;
use Coyote\Services\Grid\Grid;
use function link_to_route;

class MultiaccGrid extends Grid {
    public function buildGrid(): void {
        $this
            ->setDefaultOrder(new Order('created_at', 'desc'))
            ->addColumn('id', [
                'title'     => '#',
                'clickable' => $this->link(fn(Multiacc $acc) => $acc->id),
            ])
            ->addColumn('notes', [
                'title'     => 'Notatki moderatorskie',
                'clickable' => $this->link(fn(Multiacc $acc) => $acc->notes()->count() . ' notatek'),
            ])
            ->addColumn('multiaccUsers', [
                'title'     => 'Rozpoznane konta',
                'clickable' => $this->link(fn(Multiacc $acc) => $acc->multiaccUsers()->count() . ' kont'),
            ])
            ->addColumn('created_at', [
                'placeholder' => '--',
                'sortable'    => true,
                'title'       => 'Data dodania',
            ])
            ->addColumn('updated_at', [
                'sortable' => true,
                'title'    => 'Data aktualizacji',
            ]);
    }

    private function link(callable $title): callable {
        return function (Multiacc $acc) use ($title) {
            return link_to_route('adm.multiacc.show', $title($acc), [$acc->id]);
        };
    }
}
