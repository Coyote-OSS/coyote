<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use GuzzleHttp;
use Psr\Http\Message\ResponseInterface;

readonly class HarnessClient {
    private GuzzleHttp\Client $http;

    public function __construct(string $baseUrl) {
        $this->http = new GuzzleHttp\Client(['base_uri' => $baseUrl, 'http_errors' => false]);
    }

    public function resetCampaigns(): void {
        $response = $this->http->post('/harness/campaigns/reset');
        if ($response->getStatusCode() !== 204) {
            throw new \Exception('Failed to clear campaigns via the test harness.');
        }
    }

    public function resetJobOffers(): void {
        $response = $this->http->post('/harness/job-board/reset');
        if ($response->getStatusCode() !== 204) {
            throw new \Exception('Failed to clear job offers via the test harness.');
        }
    }

    public function createJobOffer(string $jobOfferTitle): int {
        $response = $this->http->post('/harness/job-board/job-offers', ['json' => ['title' => $jobOfferTitle]]);
        if ($response->getStatusCode() !== 201) {
            throw new \Exception('Failed to create a job offer via the test harness.');
        }
        return $this->json($response)['id'];
    }

    public function jobOfferClicks(int $jobOfferId): int {
        $response = $this->http->get("/harness/job-board/job-offers/$jobOfferId/clicks");
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to read job offer clicks via the test harness.');
        }
        return $this->json($response)['clicks'];
    }

    public function jobOfferExposures(int $jobOfferId): int {
        $response = $this->http->get("/harness/job-board/job-offers/$jobOfferId/exposures");
        if ($response->getStatusCode() === 200) {
            return $this->json($response)['exposures'];
        }
        throw new \Exception('Failed to read job offer exposures via the test harness.');
    }

    public function pinRotationSeed(int $seed): void {
        $response = $this->http->post('/harness/campaigns/rotation-seed', ['json' => ['seed' => $seed]]);
        if ($response->getStatusCode() !== 204) {
            throw new \Exception('Failed to pin the rotation seed via the test harness.');
        }
    }

    private function json(ResponseInterface $response): array {
        return \json_decode($response->getBody(), true);
    }
}
