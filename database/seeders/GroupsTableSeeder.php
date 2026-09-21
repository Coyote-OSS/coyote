<?php
namespace Database\Seeders;

use Coyote;
use Coyote\Group;
use Coyote\User;
use Illuminate\Database\Seeder;

class GroupsTableSeeder extends Seeder {
    use \SchemaBuilder;

    public function run(): void {
        $group = $this->createSystemGroup('Administrator');
        $this->createUser('admin', 'admin', 'admin@localhost', 10000, $group);
        $this->createUser('admin-lowrep', 'admin-lowrep', 'admin-lowrep@localhost', 100, $group);
    }

    private function createUser(
        string $username,
        string $password,
        string $email,
        int    $reputation,
        Group  $group): void {
        $user = new User([
            'name'       => $username,
            'email'      => $email,
            'password'   => bcrypt($password),
            'reputation' => $reputation,
        ]);
        $user->is_confirm = true;
        $user->gdpr = '{}';
        $user->save();

        Coyote\Group\User::query()->create([
            'group_id' => $group->id,
            'user_id'  => $user->id,
        ]);
    }

    private function createSystemGroup(string $groupName): Group {
        $group = new Group(['name' => $groupName]);
        $group->system = true;
        $group->save();
        return $group;
    }
}
