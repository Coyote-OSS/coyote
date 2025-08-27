<?php
namespace Database\Seeders;

use Coyote\Domain\TempEmail\TempEmailCategory;
use Coyote\TempEmail;
use Illuminate\Database\Seeder;

class TempEmailSeeder extends Seeder {
    public function run(): void {
        $this->seedTrustedDomains();
        $this->seedTemporaryDomains();
    }

    private function seedTrustedDomains(): void {
        $this->insertDomains(TempEmailCategory::TRUSTED, [
            'gmail.com', 'wp.pl', 'onet.pl', 'proton.me', 'protonmail.com',
            'icloud.com', 'live.com', 'outlook.com', 'o2.pl', 'yahoo.com',
            'op.pl', 'interia.pl',
        ]);
    }

    private function seedTemporaryDomains(): void {
        $this->insertDomains(TempEmailCategory::TEMPORARY, [
            'niepodam.pl', 'xfavaj.com',
        ]);
    }

    private function insertDomains(TempEmailCategory $category, array $domains): void {
        TempEmail::query()->insert(\array_map(
            fn(string $domain) => ['domain' => $domain, 'category' => $category->name],
            $domains));
    }
}
