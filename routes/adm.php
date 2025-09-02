<?php

use Illuminate\Routing\Router;

// logowanie do panelu administracyjnego (ponowne wpisanie hasla)
/** @var $this Router */
$this->match(['get', 'post'], 'Adm', [
    'uses'       => 'Adm\HomeController@index',
    'as'         => 'adm.home',
    'middleware' => ['auth', 'can:adm-access', 'adm:0'],
]);

$this->group(
    [
        'namespace'  => 'Adm',
        'middleware' => ['auth', 'can:adm-access', 'adm:1'],
        'prefix'     => 'Adm',
        'as'         => 'adm.',
    ],
    function () {
        /** @var $this Router */
        $this->get('Dashboard', 'DashboardController@index')->name('dashboard');
        $this->get('Exit', 'ExitController@index')->name('exit');

        $this->get('Forum/Categories', 'Forum\CategoriesController@index')->name('forum.categories');

        $this->get('Forum/Categories/Save/{id?}', [
            'uses' => 'Forum\CategoriesController@edit',
            'as'   => 'forum.categories.save',
        ]);

        $this->post('Forum/Categories/Save/{id?}', ['uses' => 'Forum\CategoriesController@save']);
        $this->get('Forum/Categories/Move/{id}', [
            'uses' => 'Forum\CategoriesController@move',
            'as'   => 'forum.categories.move',
        ]);

        $this->get('Forum/Permissions', [
            'as'         => 'forum.permissions',
            'uses'       => 'Forum\PermissionsController@index',
            'middleware' => 'can:adm-group',
        ]);

        $this->get('Forum/Permissions/Save', [
            'as'         => 'forum.permissions.save',
            'uses'       => 'Forum\PermissionsController@edit',
            'middleware' => 'can:adm-group',
        ]);

        $this->post('Forum/Permissions/Save', [
            'uses'       => 'Forum\PermissionsController@save',
            'middleware' => 'can:adm-group',
        ]);

        /*
         * Forum reasons
         */

        $this->get('Forum/Reasons', 'Forum\ReasonsController@index')->name('forum.reasons');

        $this->get('Forum/Reasons/Save/{id?}', [
            'uses' => 'Forum\ReasonsController@edit',
            'as'   => 'forum.reasons.save',
        ]);

        $this->post('Forum/Reasons/Save/{id?}', ['uses' => 'Forum\ReasonsController@save']);
        $this->post('Forum/Reasons/Delete/{id}', [
            'as'   => 'forum.reasons.delete',
            'uses' => 'Forum\ReasonsController@delete',
        ]);

        $this->get('Users', 'UsersController@index')->name('users');
        $this->get('Users/{user_trashed}', 'UsersController@show')->name('users.show');
        $this->get('Users/Save/{user_trashed}', 'UsersController@edit')->name('users.save');
        $this->post('Users/Save/{user_trashed}', 'UsersController@save');
        $this->get('Users/Inspect/{user_trashed}', 'UsersController@inspect')->name('users.inspect');
        $this->post('Users/{user_trashed}/ContentDelete', 'UsersController@contentDelete')->name('users.contentDelete');
        $this->post('Users/{user_trashed}/ShadowBan', 'UsersController@shadowBan')->name('users.shadowBan');

        $this->get('Firewall', 'FirewallController@index')->name('firewall');
        $this->get('Firewall/Save/{firewall?}', 'FirewallController@edit')->name('firewall.save');
        $this->post('Firewall/Save/{firewall?}', 'FirewallController@save');
        $this->post('Firewall/Delete/{firewall}', 'FirewallController@delete')->name('firewall.delete');

        $this->get('Stream', 'StreamController@index')->name('stream');

        $this->get('Flag', 'FlagController@index')->name('flag');

        $this->get('Groups', ['uses' => 'GroupsController@index', 'middleware' => 'can:adm-group'])->name('groups');

        $this->get('Groups/Save/{group?}', [
            'uses'       => 'GroupsController@edit',
            'middleware' => 'can:adm-group',
            'as'         => 'groups.save',
        ]);

        $this->post('Groups/Save/{group?}', [
            'uses'       => 'GroupsController@save',
            'middleware' => 'can:adm-group',
        ]);

        $this->post('Groups/Delete/{group}', [
            'uses'       => 'GroupsController@delete',
            'middleware' => 'can:adm-group',
            'as'         => 'groups.delete',
        ]);

        $this->get('Sessions', ['uses' => 'SessionsController@index'])->name('sessions');

        $this->get('Words', ['uses' => 'WordsController@index'])->name('words');
        $this->post('Words', ['uses' => 'WordsController@save'])->name('words.save');

        $this->get('Blocks', ['uses' => 'BlocksController@index'])->name('blocks');
        $this->get('Blocks/Save/{block?}', 'BlocksController@edit')->name('blocks.save');
        $this->post('Blocks/Save/{block?}', 'BlocksController@save');
        $this->post('Blocks/Delete/{block}', 'BlocksController@delete')->name('blocks.delete');

        $this->get('Payments', [
            'uses'       => 'PaymentsController@index',
            'middleware' => 'can:adm-payment',
            'as'         => 'payments',
        ]);

        $this->get('Payments/Show/{payment}', [
            'uses'       => 'PaymentsController@show',
            'middleware' => 'can:adm-payment',
            'as'         => 'payments.show',
        ]);

        $this->get('Payments/Invoice/{payment}', [
            'uses'       => 'PaymentsController@invoice',
            'middleware' => 'can:adm-payment',
            'as'         => 'payments.invoice',
        ]);

        $this->get('Payments/Paid/{payment}', [
            'uses'       => 'PaymentsController@paid',
            'middleware' => 'can:adm-payment',
            'as'         => 'payments.paid',
        ]);

        $this->get('Tags', ['uses' => 'TagsController@index'])->name('tags');
        $this->get('Tags/Save/{tag?}', 'TagsController@edit')->name('tags.save');
        $this->post('Tags/Save/{tag?}', 'TagsController@save');
        $this->post('Tags/Delete/{tag?}', 'TagsController@delete')->name('tags.delete');
        
        $this->get('Multiacc','MultiaccController@index')->name('multiacc.home');
        $this->post('Multiacc','MultiaccController@createEmpty')->name('multiacc.create');
        $this->get('Multiacc/Join','MultiaccController@form')->name('multiacc.join.form');
        $this->post('Multiacc/Join','MultiaccController@create')->name('multiacc.join.save');
        $this->get('Multiacc/{multiacc}/Show','MultiaccController@show')->name('multiacc.show');
        $this->get('Multiacc/{multiacc}/Note','MultiaccController@noteForm')->name('multiacc.noteForm');
        $this->post('Multiacc/{multiacc}/Note','MultiaccController@noteSave')->name('multiacc.noteSave');
        $this->post('Multiacc/Find','MultiaccController@findByUsername')->name('multiacc.findByUsername');
        $this->get('Multiacc/{multiacc}/Include','MultiaccController@includeUserForm')->name('multiacc.includeUser.form');
        $this->post('Multiacc/{multiacc}/Include','MultiaccController@includeUserSave')->name('multiacc.includeUser.save');
        
        $this->get('Incognito','IncognitoController@index')->name('incognito.home');
    },
);
