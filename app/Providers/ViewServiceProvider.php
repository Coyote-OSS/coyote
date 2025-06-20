<?php
namespace Coyote\Providers;

use Coyote\Domain\Clock;
use Coyote\Domain\Icon\Icons;
use Coyote\Domain\Settings\UserTheme;
use Coyote\Domain\User\UserSettings;
use Coyote\Http\Composers\InitialStateComposer;
use Coyote\Services\Forum\UserDefined;
use Coyote\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

class ViewServiceProvider extends ServiceProvider {
    public function boot(): void {
        /** @var Clock $clock */
        $clock = app(Clock::class);
        /** @var Factory $view */
        $view = $this->app['view'];
        $view->composer(['layout', 'adm.home'], InitialStateComposer::class);
        $view->composer(['layout', 'adm.home'], fn(View $view) => $view->with([
            'darkTheme' => $this->userTheme()->isThemeDark(),
        ]));
        $view->composer('layout', function (View $view) use ($clock) {
            $theme = $this->userTheme();
            $view->with([
                '__master_menu'  => $this->buildMasterMenu(),
                '__dark_theme'   => $theme->isThemeDark(),
                '__color_scheme' => $theme->themeMode(),
                'gdpr'           => [
                    'content'  => (new UserSettings)->cookieAgreement(),
                    'accepted' => $this->gdprAccepted(),
                ],
                'year'           => $clock->year(),
                'currentUser'    => $this->currentUser(),
                'icons'          => (new Icons)->icons(),
                'navigation'     => $this->navigation(),
            ]);
        });
    }

    private function gdprAccepted(): bool {
        /** @var Request $request */
        $request = $this->app['request'];
        $user = $request->user();
        if ($user) {
            /** @var User $user */
            return (bool)$user->gdpr;
        }
        return false;
    }

    private function buildMasterMenu(): Builder {
        /** @var Menu $menu */
        $menu = app(Menu::class);
        /** @var Builder $builder */
        $builder = $menu->make('__master_menu___', function (Builder $menu) {
            foreach (config('laravel-menu.__master_menu___') as $title => $data) {
                $children = array_pull($data, 'children');
                $item = $menu->add($title, $data);
                foreach ((array)$children as $key => $child) {
                    $item->add($key, $child);
                }
            }
        });

        $categories = collect($this->app[UserDefined::class]->allowedForums($this->app['request']->user()))->where('parent_id', null);
        $rendered = view('legacyComponents.mega-menu', ['sections' => $this->groupBySections($categories)])->render();

        $builder->forum->after($rendered);

        return $builder;
    }

    public function groupBySections(Support\Collection $categories): array {
        $sections = [];
        foreach ($categories as $category) {
            if ($category['section'] === null) {
                continue;
            }
            $sections[$category['section']][] = $category;
        }
        return $sections;
    }

    function currentUser(): ?array {
        if (auth()->guest()) {
            return null;
        }
        /** @var User $user */
        $user = auth()->user();
        return [
            'photo' => $user->photo,
            'name'  => $user->name,
        ];
    }

    private function userTheme(): UserTheme {
        /** @var UserTheme $theme */
        $theme = $this->app[UserTheme::class];
        return $theme;
    }

    private function navigation(): array {
        return [
            'bodyItems'   => [
                'categories' => [
                    [
                        'title' => 'Dyskusje',
                        'icon'  => 'navigationCategoryDiscussion',
                        'items' => [
                            ['title' => 'Item 1', 'count' => '1.2k'],
                            ['title' => 'Slightly longer title', 'count' => '1.2k'],
                            ['title' => 'Item 3', 'count' => '1.2k'],
                            ['title' => 'Item 4', 'count' => '1.2k'],
                            ['title' => 'Item 5', 'count' => '1.2k'],
                            ['title' => 'Item 6', 'count' => '1.2k'],
                            ['title' => 'Item 7', 'count' => '1.2k'],
                            ['title' => 'Item 8', 'count' => '1.2k'],
                            ['title' => 'Item 9', 'count' => '1.2k'],
                            ['title' => 'Item 10', 'count' => '1.2k'],
                        ],
                    ],
                    [
                        'title' => 'Społeczność',
                        'icon'  => 'navigationCategoryCommunity',
                        'items' => [
                            ['title' => 'Mikroblogi', 'count' => '1.2k', 'subtitle' => 'krótkie wpisy i dyskusje'],
                            ['title' => 'Wydarzenia', 'subtitle' => 'Zainteresowany współpracą?'],
                            ['title' => 'Off-Topic'],
                            ['title' => 'Społeczność', 'count' => '27k'],
                            ['title' => 'Coyote', 'count' => '27k'],
                        ],
                    ],
                    [
                        'title' => 'Zasoby',
                        'icon'  => 'navigationCategoryResources',
                        'items' => [
                            ['title' => 'Kompendium', 'count' => '66k', 'subtitle' => 'Kursy, artykuły i wiele innych'],
                            ['title' => 'Poradniki', 'count' => '3.5k'],
                            ['title' => 'Narzędzia', 'count' => '27k'],
                            ['title' => 'Algorytmy', 'count' => '27k'],
                        ],
                    ],
                ],
            ],
            'headerItems' => [
                ['title' => '12.5k aktywnych dyskusji', 'icon' => 'navigationActiveDiscussions'],
                ['title' => '235 online', 'icon' => 'navigationOnlineUsers'],
            ],
            'footerItems' => [
                'Najnowsze',
                'Popularne',
                'Bez odpowiedzi',
            ],
        ];
    }
}
