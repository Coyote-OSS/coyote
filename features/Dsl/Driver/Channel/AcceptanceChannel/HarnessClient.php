<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use Laravel\Dusk\Browser;

readonly class HarnessClient {
    public function __construct(private Browser $browser) {}

    public function resetCampaigns(): void {
        [$status] = $this->browser->script(<<<'JS'
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/harness/campaigns/reset', false);
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.send();
            return xhr.status;
            JS,
        );
        if ($status !== 204) {
            throw new \Exception('Failed to clear campaigns via the test harness.');
        }
    }

    public function pinRotationSeed(int $seed): void {
        [$status] = $this->browser->script(<<<JS
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/harness/campaigns/rotation-seed', false);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.send(JSON.stringify({seed: $seed}));
            return xhr.status;
            JS,
        );
        if ($status !== 204) {
            throw new \Exception('Failed to pin the rotation seed via the test harness.');
        }
    }
}
