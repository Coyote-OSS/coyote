<?php
namespace Coyote\Console\Commands;

use Coyote\Domain\TempEmail\TempEmailCategory;
use Coyote\TempEmail;
use Illuminate\Console\Command;
use Illuminate\Support;

class PurgeTempEmailCommand extends Command {
    protected $signature = 'tempemail:purge';
    protected $description = 'Update the list of temporary/disposable email domains.';

    private string $providerAccessKey = 'aHR0cHM6Ly9yYXcuZ2l0aHVidXNlcmNvbnRlbnQuY29tL2Rpc3Bvc2FibGUvZGlzcG9zYWJsZS1lbWFpbC1kb21haW5zL3JlZnMvaGVhZHMvbWFzdGVyL2RvbWFpbnMudHh0';

    public function handle(): void {
        $this->clearDomains();
        $this->insertTrustedDomains();
        $this->insertTemporaryDomains();
    }

    private function clearDomains(): void {
        $this->info('Clearing existing domains.');
        TempEmail::query()->delete();
        $this->info('Cleared.');
    }

    private function insertTrustedDomains(): void {
        $this->insertDomains(TempEmailCategory::TRUSTED, [
            'gmail.com', 'wp.pl', 'onet.pl', 'proton.me', 'protonmail.com',
            'icloud.com', 'live.com', 'outlook.com', 'o2.pl', 'yahoo.com',
            'op.pl', 'interia.pl',
        ]);
    }

    private function insertTemporaryDomains(): void {
        $this->info('Fetching updated domains.');
        $emailDomains = $this->temporaryEmailDomains();
        $this->info('Fetched.');
        $this->info('Inserting updated domains.');
        $this->bulkInsert($emailDomains);
        $this->info('Inserted.');
    }

    private function insertDomains(TempEmailCategory $category, array $domains): void {
        TempEmail::query()->insert(\array_map(
            fn(string $domain) => ['domain' => $domain, 'category' => $category->name],
            $domains));
    }

    /**
     * @return string[]
     */
    private function temporaryEmailDomains(): array {
        return \explode("\n", \file_get_contents(base64_decode($this->providerAccessKey)));
    }

    /**
     * @param string[] $emailDomains
     */
    private function bulkInsert(array $emailDomains): void {
        collect($emailDomains)
            ->chunk(1000)
            ->each(function (Support\Collection $chunk): void {
                $this->insertDomains(TempEmailCategory::TEMPORARY, $chunk->toArray());
            });
    }
}
