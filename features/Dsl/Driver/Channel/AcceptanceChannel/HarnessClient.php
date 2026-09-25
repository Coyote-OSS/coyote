<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

readonly class HarnessClient {
    private HttpClient $http;

    public function __construct(string $baseUrl) {
        $this->http = new HttpClient($baseUrl);
    }

    public function resetCampaigns(): void {
        $response = $this->http->post('/harness/campaigns/reset');
        if ($response->status !== 204) {
            throw new \Exception('Failed to clear campaigns via the test harness.');
        }
    }

    public function resetJobOffers(): void {
        $response = $this->http->post('/harness/job-board/reset');
        if ($response->status !== 204) {
            throw new \Exception('Failed to clear job offers via the test harness.');
        }
    }

    public function createJobOffer(string $jobOfferTitle): int {
        $response = $this->http->post('/harness/job-board/job-offers', ['title' => $jobOfferTitle]);
        if ($response->status === 201) {
            return $response->json()['id'];
        }
        throw new \Exception('Failed to create a job offer via the test harness.');
    }

    public function jobOfferClicks(int $jobOfferId): int {
        return $this->jobOffer($jobOfferId)['clicks'];
    }

    public function jobOfferExposures(int $jobOfferId): int {
        return $this->jobOffer($jobOfferId)['exposures'];
    }

    private function jobOffer(int $jobOfferId): array {
        $response = $this->http->get("/harness/job-board/job-offers/$jobOfferId");
        if ($response->status !== 200) {
            throw new \Exception('Failed to read a job offer via the test harness.');
        }
        return $response->json();
    }

    public function pinRotationSeed(int $seed): void {
        $response = $this->http->post('/harness/campaigns/rotation-seed', ['seed' => $seed]);
        if ($response->status !== 204) {
            throw new \Exception('Failed to pin the rotation seed via the test harness.');
        }
    }
}
