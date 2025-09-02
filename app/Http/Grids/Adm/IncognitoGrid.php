<?php
namespace Coyote\Http\Grids\Adm;

use Boduch\Grid\Order;
use Carbon\Carbon;
use Coyote;
use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Administrator\View\Mention;
use Coyote\Services\Grid\Grid;

class IncognitoGrid extends Grid {
    public function buildGrid(): void {
        $this
            ->setDefaultOrder(new Order('created_at', 'desc'))
            ->addColumn('name', [
                'title'     => 'Użytkownik',
                'clickable' => fn(Coyote\User $user) => new Mention($user->id, $user->name),
            ])
            ->addColumn('created_at', [
                'sortable'  => true,
                'title'     => 'Data rejestracji',
                'clickable' => fn(Coyote\User $user) => $this->dateHtml($user->created_at),
            ])
            ->addColumn('incognito_at', [
                'title'     => 'Przyznanie bana',
                'clickable' => $this->incognitoDate(
                    fn(Coyote\User $user) => $this->dateHtml($user->incognito_at)),
            ])
            ->addColumn('shadow_posts', [
                'title'     => 'Postów od przyznania bana',
                'clickable' => $this->incognitoDate(fn(Coyote\User $user) => $user
                    ->hasMany(Coyote\Post::class)
                    ->where('created_at', '>', $user->incognito_at)
                    ->withTrashed()
                    ->count()),
            ]);
    }

    private function incognitoDate(callable $block): \Closure {
        return function (Coyote\User $user) use ($block) {
            if ($user->incognito_at === null) {
                return '<span class="text-muted">brak informacji</span>';
            }
            return $block($user);
        };
    }

    private function dateHtml(Carbon $date): string {
        $incognitoAt = new Date($date, now());
        return \sPrintF(
            '<span title="%s">%s</span>',
            \htmlSpecialChars($incognitoAt->format()),
            $incognitoAt->ago());
    }
}
