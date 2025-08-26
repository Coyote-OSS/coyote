<?php
namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Decorators\FormatDateRelative;
use Boduch\Grid\Filters\FilterOperator;
use Boduch\Grid\Filters\Text;
use Boduch\Grid\GridHelper;
use Boduch\Grid\Order;
use Coyote\Domain\Icon\Icons;
use Coyote\Services\Grid\Grid;
use Coyote\User;

class UsersGrid extends Grid {
    public function __construct(GridHelper $gridHelper, private Icons $icons) {
        parent::__construct($gridHelper);
        $this->perPage = 50;
    }

    public function buildGrid(): void {
        $this
            ->setDefaultOrder(new Order('id', 'desc'))
            ->addColumn('name', [
                'title'     => 'Użytkownik',
                'clickable' => $this->username(...),
                'filter'    => new Text(['operator' => FilterOperator::OPERATOR_ILIKE]),
            ])
            ->addColumn('email', [
                'title'     => 'E-mail',
                'clickable' => $this->userEmail(...),
                'filter'    => new Text(['operator' => FilterOperator::OPERATOR_ILIKE]),
            ])
            ->addColumn('created_at', [
                'title'      => 'Rejestracja',
                'decorators' => [new FormatDateRelative('nigdy', shortDate:true)],
            ])
            ->addColumn('visited_at', [
                'title'      => 'Ostatnio',
                'sortable'   => true,
                'decorators' => [new FormatDateRelative('nigdy', shortDate:true)],
            ])
            ->addColumn('Status', [
                'clickable' => $this->userStatusHtml(...),
            ])
            ->addColumn('reputation', [
                'title' => 'Reputacja',
            ]);
    }

    private function username(User $user): string {
        return
            $this->icons->icon('adminUsersUser') . ' ' .
            link_to_route('adm.users.show', $user->name, [$user->id]);
    }

    private function userEmail(User $user): string {
        return "{$this->userEmailVerifiedIcon($user)} $user->email";
    }

    private function userEmailVerifiedIcon(User $user): string {
        if ($user->is_confirm) {
            return '';
        }
        $icon = $this->icons->icon('adminUsersEmailUnverified');
        return "<span title='Adres email nie został zweryfikowany.'>$icon</span>";
    }

    private function userStatusHtml(User $user): string {
        if ($user->is_blocked) {
            return 'Zbanowany';
        }
        if ($user->deleted_at !== null) {
            return 'Usunięty';
        }
        if ($user->is_incognito) {
            return 'ShadowBan';
        }
        return '';
    }
}
