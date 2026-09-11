<?php
namespace Tests\Legacy\Integration\Legacy;

use Coyote\Flag;
use Coyote\Microblog;
use Coyote\Services\Flags;
use Database\Seeders\FlagTypesTableSeeder;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture;

class FlagsTest extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use BaseFixture\Forum\Models;

    private Flags $flags;

    #[Before]
    public function initialize(): void
    {
        $this->flags = $this->laravel->app->make(Flags::class);
    }

    #[Before]
    public function removeFlags(): void
    {
        Flag::query()->delete();
    }

    #[Before]
    public function seedFlagTypes(): void
    {
        $this->laravel->app->make(FlagTypesTableSeeder::class)->run();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function acceptEmptyPermissions(): void
    {
        $this->flags->get();
    }

    #[Test]
    public function shouldReturnReports(): void
    {
        $this->driver->newPostReported(reportContent:'reported text');
        $flags = $this->flags->get();
        $this->assertSame(
            ['reported text'],
            $flags->pluck('text')->toArray());
    }

    #[Test]
    public function shouldFilterByRelation(): void
    {
        $this->driver->newPostReported();
        $this->flags->fromModels([Microblog::class]);
        $this->assertEmpty($this->flags->get()->pluck('text')->toArray());
    }

    #[Test]
    public function shouldReturnReports_ofDeletedResources(): void
    {
        $this->driver->newPostDeletedReported(reportContent:'reported text');
        $this->assertSame(
            ['reported text'],
            $this->flags->get()->pluck('text')->toArray());
    }
}
