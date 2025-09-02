<?php
namespace Coyote\Http\Controllers\Adm;

use Collective\Html\HtmlBuilder;
use Coyote\Http\Controllers\Controller;
use Lavary\Menu\Builder;
use Lavary\Menu\Item;
use Lavary\Menu\Menu;

class BaseController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb->push('Panel administracyjny', route('adm.home'));
    }

    /**
     * @inheritdoc
     */
    protected function view($view = null, $data = [])
    {
        return parent::view($view, array_merge($data, [
            'menu' => $this->buildMenu(app(Menu::class)),
        ]));
    }

    private function buildMenu(Menu $menu): Builder
    {
        return $menu->make('adm', function (Builder $menu) {
            if ($menu->all()->count() > 0) {
                return;
            }
            /** @var HtmlBuilder $html */
            $html = app('html');
            $fa = function ($icon) use ($html) {
                return $html->tag('i', '', ['class' => "fa-light $icon"]);
            };

            $menu->add('Strona główna', ['route' => 'adm.dashboard'])->prepend($fa('fa-display fa-fw'));
            $menu->divide(['class' => 'menu-group-moderator-actions']);

            $menu->add('Użytkownicy', ['route' => 'adm.users'])->prepend($fa('fa-user fa-fw'));
            $menu->add('Dodane posty', ['url' => route('adm.flag', ['filter' => 'type:post'])])->prepend($fa('fa-magnifying-glass fa-fw'));
            $menu->add('Dodane komentarze', ['url' => route('adm.flag', ['filter' => 'type:comment'])])->prepend($fa('fa-magnifying-glass fa-fw'));
            $menu->add('Dodane mikroblogi', ['url' => route('adm.flag', ['filter' => 'type:microblog'])])->prepend($fa('fa-magnifying-glass fa-fw'));
            $menu->add('Zgłoszone treści', ['url' => route('adm.flag', ['filter' => 'is:reported report:open'])])->prepend($fa('far fa-flag fa-fw'));
            $menu->add('Bany', ['route' => 'adm.firewall'])->prepend($fa('fa-user-doctor fa-fw'));
            $menu->add('ShadowBan', ['url' => route('adm.incognito.home')])->prepend($fa('fal fa-user fa-fw'));
            $menu->add('Kartoteka', ['url' => route('adm.multiacc.home')])->prepend($fa('fal fa-user fa-fw'));
            $menu->add('Aktywne sesje', ['route' => 'adm.sessions'])->prepend($fa('fa-light fa-wave-pulse fa-fw'));
            $menu->add('Dziennik zdarzeń', ['route' => 'adm.stream'])->prepend($fa('fa-newspaper fa-fw'));
            $menu->add('Cenzura', ['route' => 'adm.words'])->prepend($fa('fa-highlighter fa-fw'));
            $menu->add('Tagi', ['route' => 'adm.tags'])->prepend($fa('fa-tag fa-fw'));

            $menu->divide(['class' => 'menu-group-service-operations']);

            $menu->add('Grupy', ['route' => 'adm.groups'])->prepend($fa('fa-users fa-fw'))->data('permission', 'adm-group');
            $menu->add('Kategorie', ['route' => 'adm.forum.categories'])->prepend($fa('fa-table-list fa-fw'));
            $menu->add('Uprawnienia w kategorii', ['route' => 'adm.forum.permissions'])->prepend($fa('fa-file-signature fa-fw'))->data('permission', 'adm-group');
            $menu->add('Powody moderacji', ['route' => 'adm.forum.reasons'])->prepend($fa('fa-eraser fa-fw'));
            $menu->add('Bloki statyczne', ['route' => 'adm.blocks'])->prepend($fa('far fa-file-code fa-fw'));
            $menu->add('Faktury i płatności', ['route' => 'adm.payments'])->prepend($fa('fa-cart-shopping fa-fw'))->data('permission', 'adm-payment');
        })
            ->filter(function (Item $item): bool {
                if ($item->data('permission')) {
                    return auth()->user()->can($item->data('permission'));
                }
                return true;
            });
    }

    /**
     * Clear users cache permission after updating groups etc.
     */
    protected function flushPermission(): void
    {
        $this->getCacheFactory()->tags('permissions')->flush();
    }

    protected function getCacheFactory(): \Illuminate\Contracts\Cache\Repository
    {
        return app(\Illuminate\Contracts\Cache\Repository::class);
    }
}
