<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

readonly class HarnessClient {
    public function __construct(private BrowserDriver $driver) {}

    public function resetCampaigns(): void {
        [$status] = $this->driver->browser()->script(<<<'JS'
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

    public function resetJobOffers(): void {
        [$status] = $this->driver->browser()->script(<<<'JS'
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/harness/job-board/reset', false);
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.send();
            return xhr.status;
            JS,
        );
        if ($status !== 204) {
            throw new \Exception('Failed to clear job offers via the test harness.');
        }
    }

    public function createJobOffer(string $jobOfferTitle): int {
        $title = \json_encode($jobOfferTitle);
        [[$status, $body]] = $this->driver->browser()->script(<<<JS
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/harness/job-board/job-offers', false);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.send(JSON.stringify({title: $title}));
            return [xhr.status, xhr.responseText];
            JS,
        );
        if ($status !== 201) {
            throw new \Exception('Failed to create a job offer via the test harness.');
        }
        return \json_decode($body, true)['id'];
    }

    public function jobOfferClicks(int $jobOfferId): int {
        [[$status, $body]] = $this->driver->browser()->script(<<<JS
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '/harness/job-board/job-offers/$jobOfferId/clicks', false);
            xhr.send();
            return [xhr.status, xhr.responseText];
            JS,
        );
        if ($status !== 200) {
            throw new \Exception('Failed to read job offer clicks via the test harness.');
        }
        return \json_decode($body, true)['clicks'];
    }

    public function pinRotationSeed(int $seed): void {
        [$status] = $this->driver->browser()->script(<<<JS
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
