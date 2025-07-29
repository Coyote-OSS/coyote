<?php

/** @var $this \Illuminate\Routing\Router */

use Coyote\Http\Controllers\User\PrivacyController;

$this->group(['namespace' => 'User', 'prefix' => 'User', 'middleware' => 'auth', 'as' => 'user.'], function () {
    /** @var $this \Illuminate\Routing\Router */
    // strona glowna panelu uzytkownika
    $this->get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
    // dodawanie i usuwanie zdjecia uzytkownika
    $this->post('Photo/Upload', ['uses' => 'HomeController@upload', 'as' => 'photo.upload']);
    $this->delete('Photo/Delete', ['uses' => 'HomeController@delete', 'as' => 'photo.delete']);

    // ustawienia uzytkownika
    $this->get('Settings', ['uses' => 'SettingsController@index', 'as' => 'settings']);
    $this->post('Settings', 'SettingsController@save');

    $this->get('Notifications', ['uses' => 'NotificationsController@index', 'as' => 'notifications']);
    $this->get('Notifications/Settings', ['uses' => 'NotificationsController@settings', 'as' => 'notifications.settings']);
    $this->post('Notifications/Settings', 'NotificationsController@save');
    $this->get('Notifications/Ajax', ['uses' => 'NotificationsController@ajax', 'as' => 'notifications.ajax']);
    $this->post('Notifications/Mark', ['uses' => 'NotificationsController@markAllAsRead', 'as' => 'notifications.mark']);
    $this->delete('Notifications/Delete/{uuid}', ['uses' => 'NotificationsController@delete', 'as' => 'notifications.delete']);

    $this->get('Pm', ['uses' => 'PmController@index', 'as' => 'pm']);
    $this->get('Pm/Show/{pm}', ['uses' => 'PmController@show', 'as' => 'pm.show']);
    $this->get('Pm/Submit', ['uses' => 'PmController@submit', 'as' => 'pm.submit']);
    $this->post('Pm/Submit', 'PmController@save');
    $this->post('Pm/Mark/{pm}', ['uses' => 'PmController@mark', 'as' => 'pm.mark']);
    $this->delete('Pm/Delete/{pm}', ['uses' => 'PmController@delete', 'as' => 'pm.delete']);
    $this->delete('Pm/Trash/{id}', ['uses' => 'PmController@trash', 'as' => 'pm.trash']);
    $this->post('Pm/Preview', ['uses' => 'PmController@preview', 'as' => 'pm.preview']);
    $this->get('Pm/Inbox', ['uses' => 'PmController@inbox', 'as' => 'pm.ajax']);
    $this->get('Pm/Infinity', ['uses' => 'PmController@infinity', 'as' => 'pm.infinity']);

    $this->get('Favorites', ['uses' => 'FavoritesController@index', 'as' => 'favorites']);
    $this->get('Favorites/Forum', ['uses' => 'FavoritesController@forum', 'as' => 'favorites.forum']);
    $this->get('Favorites/Job', ['uses' => 'FavoritesController@job', 'as' => 'favorites.job']);
    $this->get('Favorites/Microblog', ['uses' => 'FavoritesController@microblog', 'as' => 'favorites.microblog']);
    $this->get('Favorites/Wiki', ['uses' => 'FavoritesController@wiki', 'as' => 'favorites.wiki']);
    $this->post('Favorites', 'FavoritesController@save');

    $this->get('Rates', ['uses' => 'RatesController@index', 'as' => 'rates']);
    $this->post('Rates', 'RatesController@save');

    $this->get('Stats', ['uses' => 'StatsController@index', 'as' => 'stats']);
    $this->post('Stats', 'StatsController@save');

    $this->get('Accepts', ['uses' => 'AcceptsController@index', 'as' => 'accepts']);
    $this->post('Accepts', 'AcceptsController@save');

    $this->get('Skills', ['uses' => 'SkillsController@index', 'as' => 'skills']);
    $this->post('Skills', 'SkillsController@save');
    $this->post('Skills/{id}', 'SkillsController@update');
    $this->delete('Skills/{id}', ['uses' => 'SkillsController@delete', 'as' => 'skills.delete']);

    $this->get('Security', ['uses' => 'SecurityController@index', 'as' => 'security']);
    $this->post('Security', 'SecurityController@save');

    $this->get('Password', ['uses' => 'PasswordController@index', 'as' => 'password']);
    $this->post('Password', 'PasswordController@save');

    $this->get('DeleteAccount', ['uses' => 'DeleteAccountController@index', 'as' => 'delete']);
    $this->post('DeleteAccount', ['uses' => 'DeleteAccountController@delete']);

    $this->get('Relations', ['uses' => 'RelationsController@showRelations', 'as' => 'relations']);
    $this->post('Block/{id}', ['uses' => 'RelationsController@block']);
    $this->post('Unblock/{id}', ['uses' => 'RelationsController@unblock']);
    $this->post('Follow/{id}', ['uses' => 'RelationsController@follow']);
    $this->post('Unfollow/{id}', ['uses' => 'RelationsController@unblock']);

    $this->get('Tokens', ['uses' => 'TokensController@index', 'as' => 'tokens']);
    $this->post('push', ['uses' => 'PushController@store']);

    $this->get('Verification', ['uses' => 'VerificationController@index', 'as' => 'verification']);
    $this->post('Verification/Initiate', ['uses' => 'VerificationController@initiateVerification']);
    $this->post('Verification/Verify', ['uses' => 'VerificationController@verify']);
});

$this->put('User/Privacy', [PrivacyController::class, 'set']);
$this->get('User/Privacy/Reset', [PrivacyController::class, 'reset']);

// wizytowka usera. komponent ktory pojawia sie po naprowadzenia kursora nad login usera
$this->get('User/Vcard/{user}', ['uses' => 'User\VcardController@index', 'as' => 'user.vcard']);
// zapis ustawien do tabeli settings. moga to byc np. niestandardowe ustawienia takie jak
// np. domyslna zakladka na stronie glownej
$this->post('User/Settings/Ajax', ['uses' => 'User\SettingsController@ajax', 'as' => 'user.settings.ajax']);

// @deprecated
$this->get('notification/{uuid}', ['uses' => 'User\NotificationsController@url'])->name('user.notifications.url');
$this->get('Notification', ['uses' => 'User\NotificationsController@redirectToUrl'])->name('user.notifications.redirect');
$this->get('ping', ['uses' => 'User\PingController@index'])->name('ping');
