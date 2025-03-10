<?php
namespace Neon\Acceptance\Test\Dsl;

readonly class Driver
{
    private SeleniumDriver $selenium;
    private string $baseUrl;

    public function __construct()
    {
        $this->selenium = new SeleniumDriver();
        $this->baseUrl = 'http://neon-acceptance:8881/';
    }

    public function close(): void
    {
        $this->selenium->close();
    }

    public function addJobOffer(string $title): void
    {
        $this->acceptanceIntegration($this->url('/integration/job-offers'), [
            'jobOfferTitle' => $title,
        ]);
    }

    public function fetchJobOffers(): array
    {
        $this->selenium->navigate($this->url('/job-offers'));
        return \explode("\n", $this->selenium->text());
    }

    private function acceptanceIntegration(string $url, array $body): void
    {
        $this->selenium->navigate($url . '?' . \http_build_query($body));
    }

    private function url(string $url): string
    {
        return \rTrim($this->baseUrl, '/') . $url;
    }
}
