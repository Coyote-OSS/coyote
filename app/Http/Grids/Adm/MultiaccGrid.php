<?php
namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Order;
use Coyote;
use Coyote\Domain\Administrator\View\Mention;
use Coyote\Models\Multiacc;
use Coyote\Services\Grid\Grid;
use Illuminate\Support\HtmlString;

class MultiaccGrid extends Grid {
    public function buildGrid(): void {
        $this
            ->setDefaultOrder(new Order('created_at', 'desc'))
            ->addColumn('id', [
                'title'     => '#',
                'clickable' => $this->link(fn(Multiacc $acc) => $acc->id),
            ])
            ->addColumn('multiaccUsers', [
                'title'     => 'Konta delikwenta',
                'clickable' => $this->link(function (Multiacc $acc) {
                    $users = $acc->multiaccUsers();
                    $userAccounts = $users->count();
                    if ($userAccounts === 0) {
                        return $this->muted('brak kont');
                    }
                    if ($userAccounts === 1) {
                        /** @var Multiacc\User $multiaccUser */
                        $multiaccUser = $users->get()->get(0);
                        return $this->mention($multiaccUser->user, $multiaccUser->multiacc_id);
                    }
                    return "$userAccounts kont(a)";
                }),
            ])
            ->addColumn('notes', [
                'title'     => 'Notatki moderatorskie',
                'clickable' => $this->link(function (Multiacc $acc) {
                    $notes = $acc->notes()->count();
                    if ($notes === 0) {
                        return $this->muted('brak notatek');
                    }
                    return "$notes notatek";
                }),
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
            return \link_to_route('adm.multiacc.show', $title($acc), [$acc->id]);
        };
    }

    private function muted(string $title): HtmlString {
        return new HtmlString('<span class="text-muted">' . \htmlSpecialChars($title) . '</span>');
    }

    private function mention(Coyote\User $user, int $multiaccId): HtmlString {
        return new HtmlString(new Mention($user->id, $user->name,
            route('adm.multiacc.show', [$multiaccId])));
    }
}
