<?php
namespace Tests\Integration\Projections\ForumJobOffers;

use Coyote\Currency;
use Coyote\Firm;
use Coyote\Job;
use Coyote\Projections\ForumJobOffers\ForumJobOffersPresenter;
use Coyote\Tag;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;
use Web\Projections\ForumJobOffers\ViewModel\ForumJobOfferTile;

#[CoversClass(ForumJobOffersPresenter::class)]
class ForumJobOffersPresenterTest extends TestCase {
    use Server\Laravel\Application;

    #[Test]
    public function hidesJobOffers_withoutPreviewQueryParameter(): void {
        $this->disablePreview();
        $this->createPublishedJob();
        $this->assertSame([], $this->presenter()->forumJobOffers());
    }

    #[Test]
    public function exposesForumJobOfferTile_withPreviewQueryParameter(): void {
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
        $this->enablePreview();

        $tile = $this->tileFor($job);

        $this->assertSame($job->firm->name, $tile->companyName);
        $this->assertStringContainsString('firm-logo.jpg', $tile->companyLogoUrl);
        $this->assertSame(route('neon.jobOffer.show', [$job->slug, $job->id]), $tile->jobOfferHref);
        $this->assertSame($job->title, $tile->jobOfferTitle);
        $this->assertSame(['Warszawa'], $tile->headerPills);
        $this->assertSame('12000 - 18000 zł brutto / miesięcznie', $tile->salary);
        $this->assertCount(1, $tile->technologyTags);
        $this->assertSame($tagName, $tile->technologyTags[0]->name);
        $this->assertStringContainsString('tag-logo.jpg', $tile->technologyTags[0]->logoUrl);
    }

    #[Test]
    public function excludesUnpublishedJobOffers(): void {
        $job = $this->createPublishedJob(['is_publish' => false]);
        $this->enablePreview();

        $this->assertJobIsMissing($job);
    }

    #[Test]
    public function excludesExpiredJobOffers(): void {
        $job = $this->createPublishedJob();
        // Job::creating() unconditionally overwrites deadline_at from the plan length,
        // so the expiry has to be forced after creation instead of through the factory.
        $job->deadline_at = now()->subDay();
        $job->save();
        $this->enablePreview();

        $this->assertJobIsMissing($job);
    }

    #[Test]
    public function formatsSalaryAsUndisclosed_whenNeitherFromNorToIsSet(): void {
        $job = $this->createPublishedJob(['salary_from' => null, 'salary_to' => null]);
        $this->enablePreview();

        $this->assertSame('Wynagrodzenie nieujawnione', $this->tileFor($job)->salary);
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
        $this->enablePreview();

        $this->assertSame('od 10000 zł netto / miesięcznie', $this->tileFor($job)->salary);
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
        $this->enablePreview();

        $this->assertSame('do 9000 zł netto / godzinowo', $this->tileFor($job)->salary);
    }

    #[Test]
    public function includesRemotePill_whenJobIsRemote(): void {
        $job = $this->createPublishedJob(['is_remote' => true]);
        $this->enablePreview();

        $this->assertSame(['Remote'], $this->tileFor($job)->headerPills);
    }

    #[Test]
    public function omitsHeaderPills_whenJobHasNoLocationsAndIsNotRemote(): void {
        $job = $this->createPublishedJob(['is_remote' => false]);
        $this->enablePreview();

        $this->assertSame([], $this->tileFor($job)->headerPills);
    }

    #[Test]
    public function companyLogoUrlIsNull_whenFirmHasNoLogo(): void {
        $job = $this->createPublishedJob();
        $this->enablePreview();

        $this->assertNull($this->tileFor($job)->companyLogoUrl);
    }

    #[Test]
    public function companyLogoUrlIsPresent_whenFirmHasLogo(): void {
        $job = $this->createPublishedJob();
        $job->firm->setAttribute('logo', 'firm-logo.jpg');
        $job->firm->save();
        $this->enablePreview();

        $this->assertStringContainsString('firm-logo.jpg', $this->tileFor($job)->companyLogoUrl);
    }

    #[Test]
    public function technologyTagLogoUrlIsNull_whenTagHasNoLogo(): void {
        $job = $this->createPublishedJob();
        $tag = Tag::query()->create(['name' => $this->uniqueTagName()]);
        $job->tags()->sync([$tag->id]);
        $this->enablePreview();

        $this->assertNull($this->tileFor($job)->technologyTags[0]->logoUrl);
    }

    private function createPublishedJob(array $overrides = []): Job {
        // Job::creating() always sets deadline_at from the plan length, so a fresh job
        // is never expired by default; excludesExpiredJobOffers() overrides it afterwards.
        /** @var Job $job */
        $job = factory(Job::class)->create(['is_publish' => true, ...$overrides]);
        // the legacy 'firm' factory state only associates in-memory and never persists firm_id,
        // so the firm is attached and saved explicitly here instead.
        $job->firm()->associate(factory(Firm::class)->create(['user_id' => $job->user_id]));
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

    private function disablePreview(): void {
        $this->bindRequest([]);
    }

    private function enablePreview(): void {
        $this->bindRequest(['preview' => 'true']);
    }

    private function bindRequest(array $query): void {
        $this->laravel->app->instance('request', Request::create('/', 'GET', $query));
    }
}
