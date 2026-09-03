<?php
namespace Tests\Integration\Projections\ForumJobOffers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Coyote\Currency;
use Coyote\Firm;
use Coyote\Job;
use Coyote\Projections\ForumJobOffers\ForumJobOffersPresenter;
use Coyote\Tag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;
use Web\Projections\ForumJobOffers\ViewModel\ForumJobOfferTile;

#[CoversClass(ForumJobOffersPresenter::class)]
class ForumJobOffersPresenterTest extends TestCase {
    use Server\Laravel\Application;

    #[Test]
    public function exposesForumJobOfferTile(): void {
        $job = $this->createPublishedJob([
            'salary_from' => 12000,
            'salary_to'   => 18000,
            'currency_id' => Currency::PLN,
            'rate'        => Job::MONTHLY,
            'is_gross'    => true,
            'is_remote'   => false,
        ]);
        $job->firm->setAttribute('logo', 'firm-logo.jpg');
        $job->firm->save();
        $job->locations()->create(['city' => 'Warszawa']);
        $tagName = $this->uniqueTagName();
        $tag = Tag::query()->create(['name' => $tagName]);
        $tag->setAttribute('logo', 'tag-logo.jpg');
        $tag->save();
        $job->tags()->sync([$tag->id]);

        $tile = $this->tileFor($job);

        $this->assertSame($job->firm->name, $tile->companyName);
        $this->assertStringContainsString('firm-logo.jpg', $tile->companyLogoUrl);
        $this->assertSame(route('neon.jobOffer.show', [$job->slug, $job->id]), $tile->jobOfferHref);
        $this->assertSame($job->title, $tile->jobOfferTitle);
        $this->assertSame(['Warszawa'], $tile->headerPills);
        $this->assertSame('12000 - 18000 zł brutto / miesięcznie', $tile->salaryFormat);
        $this->assertCount(1, $tile->technologyTags);
        $this->assertSame($tagName, $tile->technologyTags[0]->name);
        $this->assertStringContainsString('tag-logo.jpg', $tile->technologyTags[0]->logoUrl);
    }

    #[Test]
    public function excludesUnpublishedJobOffers(): void {
        $job = $this->createPublishedJob(['is_publish' => false]);

        $this->assertJobIsMissing($job);
    }

    #[Test]
    public function excludesExpiredJobOffers(): void {
        $job = $this->createPublishedJob();
        // Job::creating() unconditionally overwrites deadline_at from the plan length,
        // so the expiry has to be forced after creation instead of through the factory.
        $job->deadline_at = now()->subDay();
        $job->save();

        $this->assertJobIsMissing($job);
    }

    #[Test]
    public function formatsSalaryAsUndisclosed_whenNeitherFromNorToIsSet(): void {
        $job = $this->createPublishedJob(['salary_from' => null, 'salary_to' => null]);

        $this->assertSame('Nie podano $$$', $this->tileFor($job)->salaryFormat);
    }

    #[Test]
    public function formatsSalaryRange_whenOnlyFromIsSet(): void {
        $job = $this->createPublishedJob([
            'salary_from' => 10000,
            'salary_to'   => null,
            'currency_id' => Currency::PLN,
            'rate'        => Job::MONTHLY,
            'is_gross'    => false,
        ]);

        $this->assertSame('od 10000 zł netto / miesięcznie', $this->tileFor($job)->salaryFormat);
    }

    #[Test]
    public function formatsSalaryRange_whenOnlyToIsSet(): void {
        $job = $this->createPublishedJob([
            'salary_from' => null,
            'salary_to'   => 9000,
            'currency_id' => Currency::PLN,
            'rate'        => Job::HOURLY,
            'is_gross'    => false,
        ]);

        $this->assertSame('do 9000 zł netto / godzinowo', $this->tileFor($job)->salaryFormat);
    }

    #[Test]
    public function includesRemotePill_whenJobIsRemote(): void {
        $job = $this->createPublishedJob(['is_remote' => true]);

        $this->assertSame(['Remote'], $this->tileFor($job)->headerPills);
    }

    #[Test]
    public function omitsHeaderPills_whenJobHasNoLocationsAndIsNotRemote(): void {
        $job = $this->createPublishedJob(['is_remote' => false]);

        $this->assertSame([], $this->tileFor($job)->headerPills);
    }

    #[Test]
    public function companyLogoUrlIsNull_whenFirmHasNoLogo(): void {
        $job = $this->createPublishedJob();

        $this->assertNull($this->tileFor($job)->companyLogoUrl);
    }

    #[Test]
    public function companyLogoUrlIsPresent_whenFirmHasLogo(): void {
        $job = $this->createPublishedJob();
        $job->firm->setAttribute('logo', 'firm-logo.jpg');
        $job->firm->save();

        $this->assertStringContainsString('firm-logo.jpg', $this->tileFor($job)->companyLogoUrl);
    }

    #[Test]
    public function isNew_whenBoostedWithinTwoDays(): void {
        $job = $this->createPublishedJob(boostAt:now()->subDay());
        $this->assertTrue($this->tileFor($job)->isNew);
    }

    #[Test]
    public function isNotNew_whenBoostedMoreThanTwoDaysAgo(): void {
        $job = $this->createPublishedJob(boostAt:now()->subDays(3));
        $this->assertFalse($this->tileFor($job)->isNew);
    }

    #[Test]
    public function fixtureTest_createsPublishedJobWithBoostAt(): void {
        $job = $this->createPublishedJob(boostAt:Carbon::createFromTimestamp(1704112000));
        $this->assertSame(1704112000, $job->boost_at->getTimestamp());
    }

    #[Test]
    public function technologyTagLogoUrlIsNull_whenTagHasNoLogo(): void {
        $job = $this->createPublishedJob();
        $tag = Tag::query()->create(['name' => $this->uniqueTagName()]);
        $job->tags()->sync([$tag->id]);

        $this->assertNull($this->tileFor($job)->technologyTags[0]->logoUrl);
    }

    private function createPublishedJob(
        array                $attributes = [],
        CarbonInterface|null $boostAt = null,
    ): Job {
        // Job::creating() always sets deadline_at from the plan length and boost_at to now,
        // so a fresh job is never expired or stale by default; a requested override for
        // either has to be forced after creation instead of through the factory.
        /** @var Job $job */
        $job = factory(Job::class)->create(['is_publish' => true, ...$attributes]);
        // the legacy 'firm' factory state only associates in-memory and never persists firm_id,
        // so the firm is attached and saved explicitly here instead.
        $job->firm()->associate(factory(Firm::class)->create(['user_id' => $job->user_id]));
        if ($boostAt !== null) {
            $job->boost_at = $boostAt;
        }
        $job->save();
        return $job->fresh();
    }

    private function uniqueTagName(): string {
        return 'test-tag-' . \uniqId();
    }

    private function tileFor(Job $job): ForumJobOfferTile {
        $href = route('neon.jobOffer.show', [$job->slug, $job->id]);
        foreach ($this->tiles() as $tile) {
            if ($tile->jobOfferHref === $href) {
                return $tile;
            }
        }
        $this->fail("No ForumJobOfferTile found for job #$job->id");
    }

    private function assertJobIsMissing(Job $job): void {
        $href = route('neon.jobOffer.show', [$job->slug, $job->id]);
        foreach ($this->tiles() as $tile) {
            $this->assertNotSame($href, $tile->jobOfferHref);
        }
    }

    /**
     * @return ForumJobOfferTile[]
     */
    private function tiles(): array {
        return $this->presenter()->forumJobOffers();
    }

    private function presenter(): ForumJobOffersPresenter {
        return $this->laravel->app->make(ForumJobOffersPresenter::class);
    }
}
