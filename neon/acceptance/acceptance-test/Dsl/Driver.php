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

    public function addJobOffer(
        string  $title,
        ?string $publishDate = null,
        ?int    $salaryTo = null,
        ?string $workMode = null,
        ?string $location = null,
    ): void
    {
        $this->acceptanceIntegration($this->url('/integration/job-offers'), [
            'jobOfferTitle'       => $title,
            'jobOfferPublishDate' => $publishDate ?? '2000-01-01',
            'jobOfferSalaryTo'    => $salaryTo ?? 1000,
            'jobOfferWorkMode'    => $workMode ?? 'stationary',
            'jobOfferLocations'   => $location ? [$location] : [],
        ]);
    }

    public function visitJobOffers(): void
    {
        $this->selenium->navigate($this->url('/'));
    }

    public function fetchJobOffers(): array
    {
        return array_map(
            fn(SeleniumElement $element) => $element->text(),
            $this->selenium->elements('jobOfferTitle'));
    }

    private function acceptanceIntegration(string $url, array $body): void
    {
        $this->selenium->navigate($url . '?' . \http_build_query($body));
    }

    private function url(string $url): string
    {
        return \rTrim($this->baseUrl, '/') . $url;
    }

    public function sortJobOffersByDate(): void
    {
        $this->selenium->element('jobOfferSort')->select('Najnowsze');
        $this->selenium->element('jobOfferSearch')->click();
    }

    public function sortJobOffersByHighestSalary(): void
    {
        $this->selenium->element('jobOfferSort')->select('Najwyższe wynagrodzenie');
        $this->selenium->element('jobOfferSearch')->click();
    }

    public function filterJobOffers(string $searchPhrase): void
    {
        $this->selenium->element('jobOfferSearchPhrase')->fill($searchPhrase);
        $this->selenium->element('jobOfferSearch')->click();
    }

    public function filterJobOffersBySalary(int $minimumSalary): void
    {
        $this->selenium->element('jobOfferMinimumSalary')->select("$minimumSalary");
        $this->selenium->element('jobOfferSearch')->click();
    }

    public function filterJobOffersByWorkMode(string $workMode): void
    {
        if ($workMode === 'fullyRemote') {
            $this->selenium->element('jobOfferWorkMode')->click();
            $this->selenium->label('Praca zdalna')->check(true);
        }
        if ($workMode === 'hybrid') {
            $this->selenium->element('jobOfferWorkMode')->click();
            $this->selenium->label('Praca hybrydowa')->check(true);
        }
        $this->selenium->element('jobOfferSearch')->click();
    }

    public function filterJobOffersByLocation(string $location): void
    {
        $this->selenium->element('jobOfferLocation')->click();
        $this->selenium->label($location)->click();
        $this->selenium->element('jobOfferSearch')->click();
    }

    public function clearFilters(): void
    {
        $this->selenium->element('jobOfferClearFilters')->click();
    }

    public function jobOffersOrder(string $offer1, string $offer2): int
    {
        $actual = $this->fetchJobOffersOnly([$offer1, $offer2]);
        if ($actual === [$offer1, $offer2]) {
            return 1;
        }
        if ($actual === [$offer2, $offer1]) {
            return -1;
        }
        throw new \Exception('Failed to assert order of job offers, job offers not found in list.');
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

    private function fetchJobOffersOnly(array $offers): array
    {
        $order = [];
        foreach ($this->fetchJobOffers() as $offer) {
            if (\in_array($offer, $offers)) {
                $order[] = $offer;
            }
        }
        return $order;
    }
}
