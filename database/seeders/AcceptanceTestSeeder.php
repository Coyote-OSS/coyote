<?php
namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class AcceptanceTestSeeder extends Seeder {
    public function run(): void {
        Model::unguard();
        $this->call(GroupsTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(ForumsTableSeeder::class);
        $this->call(AcceptanceUsersTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(TopicsTableSeeder::class);
        $this->call(ModernPlansTableSeeder::class);
    }
}
