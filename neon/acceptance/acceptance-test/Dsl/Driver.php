<?php
namespace Neon\Acceptance\Test\Dsl;

use Neon\Acceptance\Test\Dsl\Internal\SeleniumDriver;

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
        $root = $this->selenium->shadowRootChild('#neon-application');
        return \explode("\n", $root->text());
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
