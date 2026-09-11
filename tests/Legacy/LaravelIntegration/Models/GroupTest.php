<?php

namespace Tests\Legacy\LaravelIntegration\Models;

use Coyote\Group;
use Coyote\Permission;
use Coyote\User;
use Faker\Factory;
use Tests\Legacy\LaravelIntegration\TestCase;

class GroupTest extends TestCase
{
    public function testCreateGroup()
    {
        $group = factory(Group::class)->create();

        $user = factory(User::class)->create();

        $group->users()->attach($user);

        $faker = Factory::create();
        // trigger will fill all necessary fields in db
        $permission = Permission::forceCreate(['name' => $faker->name]);

        $this->assertDatabaseHas('group_permissions', ['group_id' => $group->id, 'permission_id' => $permission->id]);
    }
}
