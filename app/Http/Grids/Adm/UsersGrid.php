<?php

namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Filters\Select;
use Boduch\Grid\Filters\Text;
use Coyote\Services\Grid\Components\FirewallButton;
use Coyote\Services\Grid\Grid;
use Boduch\Grid\Decorators\Boolean;
use Boduch\Grid\Decorators\Ip;
use Boduch\Grid\Order;
use Boduch\Grid\Components\EditButton;
use Coyote\User;

class UsersGrid extends Grid
{
    const YES = 1;
    const NO = 0;

    public function buildGrid()
    {
        $booleanOptions = [self::YES => 'Tak', self::NO => 'Nie'];

        $this
            ->setDefaultOrder(new Order('id', 'desc'))
            ->addColumn('id', [
                'title' => 'ID',
                'sortable' => true
            ])
            ->addColumn('name', [
                'title' => 'Nazwa użytkownika',
                'sortable' => true,
                'clickable' => function (User $user) {
                    return link_to_route('adm.users.save', $user->name, [$user->id]);
                },
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE])
            ])
            ->addColumn('email', [
                'title' => 'E-mail',
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE])
            ])
            ->addColumn('created_at', [
                'title' => 'Data rejestracji'
            ])
            ->addColumn('visited_at', [
                'title' => 'Data ost. wizyty',
                'sortable' => true
            ])
            ->addColumn('is_active', [
                'title' => 'Aktywny',
                'decorators' => [new Boolean()],
                'filter' => new Select(['options' => $booleanOptions])
            ])
            ->addColumn('is_blocked', [
                'title' => 'Zablokowany',
                'decorators' => [new Boolean()],
                'filter' => new Select(['options' => $booleanOptions])
            ])
            ->addColumn('ip', [
                'title' => 'IP',
                'decorators' => [new Ip()],
                'filter' => new Text(['operator' => FilterOperator::OPERATOR_ILIKE])
            ])
            ->addColumn('reputation', [
                'title' => 'Reputacja',
                'sortable' => true
            ])
            ->addRowAction(new FirewallButton(function (User $user) {
                return route('adm.firewall.save') . '?' . http_build_query(['user' => $user->id, 'ip' => $user->ip]);
            }))
            ->addRowAction(new EditButton(function (User $user) {
                return route('adm.users.save', [$user->id]);
            }));
    }
}
