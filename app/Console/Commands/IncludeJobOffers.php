<?php
namespace Coyote\Console\Commands;

use Coyote\Services\Media\Factory;
use Coyote\Services\Media\Logo;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Http\UploadedFile;
use Neon2\Coyote\Integration;
use Neon2\JobBoard\Currency;
use Neon2\JobBoard\LegalForm;
use Neon2\JobBoard\Rate;
use Neon2\JobBoard\WorkExperience;
use Neon2\Request\ApplicationMode;
use Neon2\Request\HiringType;
use Neon2\Request\JobOfferLocation;
use Neon2\Request\JobOfferSubmit;
use Symfony\Component\HttpKernel\Exception\HttpException;

class IncludeJobOffers extends Command {
    protected $signature = 'job:include {filename}';
    protected $description = 'Include job offers.';

    public function handle(): void {
        $this->handleCommand($this->argument('filename'));
        $this->info("Done.");
    }

    private function handleCommand(string $filename): void {
        $items = $this->loadObjects($filename);
        $size = count($items);
        foreach ($items as $index => $item) {
            try {
                $this->insertObject($item);
                $finished = $index + 1;
                $this->info("Job offer included {$finished}/$size.");
            } catch (LogoDownloadForbidden $exception) {
                $this->warn("Failed to download image $finished (forbidden): {$exception->logoUrl}");
            }
        }
    }

    private function loadObjects(string $filename): array {
        return $this->sqlite($filename)
            ->table('offers_coyote')
            ->get()
            ->map(function (\stdClass $record): array {
                $values = (array)$record;
                return [
                    ...$values,
                    'tags' => json_decode($values['tags']),
                ];
            })
            ->toArray();
    }

    private function sqlite(string $filename): Connection {
        $factory = app(ConnectionFactory::class);
        return $factory->make(['driver' => 'sqlite', 'database' => $filename, 'prefix' => ''], 'sqlite');
    }

    private function insertObject(array $item): void {
        $jobOffer = $this->jobOfferSubmit($item);
        /** @var Integration $integration */
        $integration = app(Integration::class);
        auth()->loginUsingId(1);
        try {
            $integration->createJobOffer('free', $jobOffer, false, true);
        } catch (HttpException $exception) {
            $this->error($exception->getMessage());
            throw $exception;
        }
    }

    private function jobOfferSubmit(array $item): JobOfferSubmit {
        $salary = $this->withSalaryRounded($this->withMonthlyRate($this->itemSalary($item)), 250);
        $tagNames = $item['tags'];
        return new JobOfferSubmit(
            $item['title'],
            $item['html_description'],
            $salary->rangeFrom,
            $salary->rangeTo,
            false,
            Currency::from($item['salary_currency'] ?? 'USD'),
            $salary->rate,
            $this->locations($item),
            $tagNames,
            \array_map(fn(string $tag) => 0, $tagNames),
            100,
            LegalForm::from($item['legal_form']),
            WorkExperience::from($item['work_experience']),
            ApplicationMode::ExternalAts,
            null,
            $item['apply_url'],
            $item['company_name'],
            $this->companyLogoUrl($item),
            null,
            null,
            [],
            null,
            null,
            null,
            null,
            HiringType::from($item['hiring_type']));
    }

    private function itemSalary(array $item): Salary {
        return new Salary(
            $item['salary_range_from'],
            $item['salary_range_to'],
            Rate::from($item['salary_rate']));
    }

    private function companyLogoUrl(array $item): string {
        $file = $this->downloadFile($item['logo_url']);
        return $this->uploadCompanyLogo($file);
    }

    private function downloadFile(string $logoUrl): UploadedFile {
        return $this->temporaryFile($this->downloadLogo($logoUrl));
    }

    private function downloadLogo(string $logoUrl): string|false {
        try {
            return file_get_contents($logoUrl);
        } catch (\ErrorException $error) {
            if (\str_contains($error->getMessage(), '403 Forbidden')) {
                throw new LogoDownloadForbidden($logoUrl);
            }
            throw $error;
        }
    }

    private function temporaryFile(string $content): UploadedFile {
        $path = \tempNam(sys_get_temp_dir(), 'upload_');
        \file_put_contents($path, $content);
        return new UploadedFile($path, 'filename.txt');
    }

    private function uploadCompanyLogo(UploadedFile $file): string {
        /** @var Factory $factory */
        $factory = app(Factory::class);
        /** @var Logo $logo */
        $logo = $factory->make('logo');
        $logo->uploadFile('', $file);
        return (string)url((string)$logo->url());
    }

    private function withMonthlyRate(Salary $salary): Salary {
        if ($salary->rate === Rate::Yearly) {
            return new Salary(
                round($salary->rangeFrom / 12),
                round($salary->rangeTo / 12),
                Rate::Monthly);
        }
        return $salary;
    }

    private function withSalaryRounded(Salary $salary, int $closestDivisor): Salary {
        return new Salary(
            round($salary->rangeFrom / $closestDivisor) * $closestDivisor,
            round($salary->rangeTo / $closestDivisor) * $closestDivisor,
            $salary->rate);
    }

    private function locations(array $item): array {
        if ($item['city'] == null) {
            return [];
        }
        return \array_map(
            $this->city(...),
            \array_unique(explode(', ', $item['city'])));
    }

    private function city(string $city): JobOfferLocation {
        return new JobOfferLocation(
            0,
            0,
            $city,
            null,
            null,
            null,
            null);
    }
}
