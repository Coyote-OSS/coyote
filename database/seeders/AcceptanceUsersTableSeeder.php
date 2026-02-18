<?php
namespace Database\Seeders;

use Coyote;
use Coyote\Group;
use Coyote\Permission;
use Coyote\User;
use Illuminate\Database\Seeder;

class AcceptanceUsersTableSeeder extends Seeder {
    use \SchemaBuilder;

    public function run(): void {
        $group = $this->createAcceptanceGroup();
        foreach (range(1, 10) as $index) {
            $this->createUser("acceptance-test-$index", $group);
        }
        $this->createUser("acceptance-test-122", $group);
        $this->createUser("acceptance-test-123", $group);
    }

    private function createUser(string $username, Group $group): void {
        $user = new User([
            'name'       => $username,
            'email'      => "$username@localhost",
            'password'   => bcrypt($username),
            'reputation' => 100,
        ]);
        $user->is_confirm = true;
        $user->gdpr = '{}';
        $user->save();
        Coyote\Group\User::query()->create([
            'group_id' => $group->id,
            'user_id'  => $user->id,
        ]);
    }

    private function createAcceptanceGroup(): Group {
        $group = new Group(['name' => 'Acceptance test']);
        $group->save();
        /** @var Permission $alphaAccess */
        $alphaAccess = Permission::query()->where('name', 'alpha-access')->first();
        $this->db->table('group_permissions')
            ->where('group_id', $group->id)
            ->where('permission_id', $alphaAccess->id)
            ->update(['value' => true]);
        return $group;
    }
}
