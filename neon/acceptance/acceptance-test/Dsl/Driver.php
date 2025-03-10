<?php
namespace Neon\Acceptance\Test\Dsl;

use Neon\Acceptance\Test\Dsl\Internal\SeleniumDriver;
use Neon\Acceptance\Test\Dsl\Internal\SeleniumElement;

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
        $this->selenium->navigate($this->url('/job-offers'));
    }

    public function fetchJobOffers(): array
    {
        return \explode("\n", $this->applicationRoot()->text());
    }

    public function search(string $searchPhrase): void
    {
        $this->selenium->navigate($this->url('/job-offers'));
        $root = $this->applicationRoot();
        $root->byPlaceholder('Szukaj po tytule, nazwie firmy')->fill($searchPhrase);
        $root->byTestId('search')->click();
    }

    private function applicationRoot(): SeleniumElement
    {
        return $this->selenium->shadowRootChild('#neon-application');
    }

    private function acceptanceIntegration(string $url, array $body): void
    {
        $this->selenium->navigate($url . '?' . \http_build_query($body));
    }

    private function url(string $url): string
    {
        return \rTrim($this->baseUrl, '/') . $url;
    }

    public function includeDiagnosticArtifact(string $name): void
    {
        $this->saveScreenshot("$name.screenshot.png");
    }

    private function saveScreenshot(string $filename): void
    {
        $this->selenium->saveScreenshot($this->parentPath($filename));
    }

    private function parentPath(string $filename): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $filename;
    }
}
