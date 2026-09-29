<?php
namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class IntegrationTestSeeder extends Seeder {
    public function run(): void {
        $this->call(ForumsTableSeeder::class);
        $this->call(ModernPlansTableSeeder::class);
        $this->call(CurrenciesTableSeeder::class);
    }
}
