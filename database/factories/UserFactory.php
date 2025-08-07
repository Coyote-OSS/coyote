<?php

use Coyote\Group;
use Coyote\Permission;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(\Coyote\User::class, function (Faker $faker) {
    return [
        'name'                 => $faker->userName . $faker->randomNumber(3),
        'email'                => $faker->unique()->safeEmail,
        'password'             => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm',
        'is_confirm'           => true,
        'alert_login'          => true,
        'guest_id'             => $faker->uuid,
        'allow_subscribe'      => true,
        'visited_at'           => now(),
        'sig'                  => 'Lorem ipsum dolor sit amet.',
        'pm_unread'            => 0,
        'notifications_unread' => 0,
    ];
});

$factory->state(\Coyote\User::class, 'id', function (Faker $faker) {
    return [
        'id' => $faker->numberBetween(10000000),
    ];
});

$factory->state(\Coyote\User::class, 'canMentionUsers', ['reputation' => \Coyote\Reputation::USER_MENTION]);

$factory->afterCreatingState(\Coyote\User::class, 'admin', function (\Coyote\User $user) {
    /** @var Group $group */
    $group = factory(Group::class)->create();
    $group->users()->attach($user->id);
    $permissions = Permission::all();
    foreach ($permissions as $permission) {
        $group->permissions()->attach($permission->id, ['value' => 1]);
    }
});

$factory->afterCreatingState(\Coyote\User::class, 'alpha', function (\Coyote\User $user) {
    $permission = Permission::query()->where('name', 'alpha-access')->first();

    /** @var Group $group */
    $group = factory(Group::class)->create();
    $group->users()->attach($user->id);
    $group->permissions()->attach($permission->id, ['value' => 1]);
});

$factory->state(\Coyote\User::class, 'blocked', ['is_blocked' => true]);
$factory->state(\Coyote\User::class, 'deleted', ['deleted_at' => now()]);
