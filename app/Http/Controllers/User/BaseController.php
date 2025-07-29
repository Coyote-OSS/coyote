<?php
namespace Coyote\Http\Controllers\User;

use Coyote\Domain\User\MenuItem;
use Coyote\Domain\User\User;
use Coyote\Http\Controllers\Controller;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

abstract class BaseController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->breadcrumb->push('Moje konto', route('user.home'));
    }

    protected function view($view = null, $data = []) {
        return parent::view($view, [
            'side_menu' => $this->sideMenu(),
            ...$data,
        ]);
    }

    private function sideMenu(): Builder {
        /** @var Menu $menu */
        $menu = app(Menu::class);
        return $menu->make('user.settings', function (Builder $builder) {
            foreach ($this->settingsMenu($this->laravelUser()) as $menuItems) {
                foreach ($menuItems as $menuItem) {
                    $builder->add($menuItem->title, [
                        'id'        => $menuItem->htmlId,
                        'class'     => 'action-link ' . ($menuItem->htmlClass ?? ''),
                        'route'     => $menuItem->route,
                        'icon'      => $menuItem->icon,
                        'subscript' => $menuItem->subscript,
                    ]);
                }
                $builder->divide();
            }
        });
    }

    /**
     * @return MenuItem[][]
     */
    public function settingsMenu(User $user): array {
        return [
            [
                new MenuItem(
                    'Moje konto',
                    'user.home',
                    htmlId:'btn-start',
                    icon:'userAccount.userAccount'),
                new MenuItem('Weryfikacja użytkownika',
                    'user.verification',
                    htmlId:'btn-favorites',
                    icon:'userAccount.verification'),
                new MenuItem('Twoje umiejętności',
                    'user.skills',
                    htmlId:'btn-skills',
                    icon:'userAccount.skills'),
                new MenuItem(
                    'Wiadomości prywatne',
                    'user.pm',
                    subscript:"($user->privateMessagesUnread/$user->privateMessages)",
                    htmlId:'btn-pm',
                    icon:'userAccount.privateMessageList'),
                new MenuItem(
                    'Powiadomienia',
                    'user.notifications',
                    htmlId:'btn-notifies',
                    icon:'userAccount.notificationList'),
                new MenuItem(
                    'Oceny moich postów',
                    'user.rates',
                    htmlId:'btn-rates',
                    icon:'userAccount.postVotes'),
                new MenuItem(
                    'Statystyki moich postów',
                    'user.stats',
                    htmlId:'btn-stats',
                    icon:'userAccount.postCategories'),
                new MenuItem(
                    'Zaakceptowane odpowiedzi',
                    'user.accepts',
                    htmlId:'btn-accepts',
                    icon:'userAccount.postAccepts'),
                new MenuItem(
                    'Obserwowane strony',
                    'user.favorites',
                    htmlId:'btn-favorites',
                    icon:'userAccount.subscribedPages'),
                new MenuItem('Zablokowani oraz obserwowani',
                    'user.relations',
                    htmlId:'btn-favorites',
                    icon:'userAccount.relations'),
                new MenuItem('Ustawienia powiadomień',
                    'user.notifications.settings',
                    htmlId:'btn-favorites',
                    icon:'userAccount.notificationSettings'),
                new MenuItem('Ustawienia konta',
                    'user.settings',
                    htmlId:'btn-start',
                    icon:'userAccount.miscellaneousSettings'),
                new MenuItem('Zmiana hasła',
                    'user.password',
                    htmlId:'btn-pm',
                    icon:'userAccount.passwordChange'),
                new MenuItem('Dostęp',
                    'user.security',
                    htmlId:'btn-notifies',
                    icon:'userAccount.access'),
                new MenuItem('Tokeny API',
                    'user.tokens',
                    htmlId:'btn-api-tokens',
                    icon:'userAccount.apiTokens'),
                new MenuItem(
                    'Usuń konto',
                    'user.delete',
                    htmlClass:'text-danger',
                    icon:'userAccount.accountDelete'),
            ],
        ];
    }

    private function laravelUser(): User {
        /** @var \Coyote\User $user */
        $user = auth()->user();
        return new User($user->pm, $user->pm_unread);
    }
}
