<?php

namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Components\EditButton;
use Boduch\Grid\Decorators\Boolean;
use Boduch\Grid\Decorators\FormatDateRelative;
use Boduch\Grid\Order;
use Coyote\Block;
use Coyote\Services\Grid\Components\CreateButton;
use Coyote\Services\Grid\Grid;

class BlockGrid extends Grid {
    public function buildGrid() {
        $this
            ->setDefaultOrder(new Order('updated_at', 'desc'))
            ->addColumn('id', [
                'title'     => 'ID',
                'sortable'  => true,
                'clickable' => function (Block $row) {
                    return link_to_route('adm.blocks.save', $row->id, [$row->id]);
                },
            ])
            ->addColumn('name', [
                'title'     => 'Nazwa bloku',
                'clickable' => function (Block $row) {
                    return link_to_route('adm.blocks.save', $row->name, [$row->id]);
                },
            ])
            ->addColumn('created_at', [
                'title'      => 'Data utworzenia',
                'sortable'   => true,
                'decorators' => [new FormatDateRelative('nigdy')],
            ])
            ->addColumn('updated_at', [
                'title'      => 'Data modyfikacji',
                'sortable'   => true,
                'decorators' => [new FormatDateRelative('nigdy')],
            ])
            ->addColumn('is_enabled', [
                'title'      => 'Włączony',
                'decorators' => [new Boolean()],
            ])
            ->addRowAction(new EditButton(function (Block $row) {
                return route('adm.blocks.save', [$row->id]);
            }))
            ->addComponent(new CreateButton(
                route('adm.blocks.save'),
                'Nowy blok',
            ));
    }
}
