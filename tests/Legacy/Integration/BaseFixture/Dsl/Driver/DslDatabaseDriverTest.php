<?php
namespace Tests\Legacy\Integration\BaseFixture\Dsl\Driver;

use Coyote\Forum;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel;

class DslDatabaseDriverTest extends TestCase
{
    use Laravel\Application;
    use Laravel\Transactional;

    private DslDatabaseDriver $driver;

    #[Before]
    public function initialize(): void
    {
        $this->driver = new DslDatabaseDriver($this->laravel);
    }

    #[Test]
    public function seedsCategory_ifNotExists(): void
    {
        $this->driver->seedCategoryIfNotExists('New');
        $this->laravel->assertSeeInDatabase('forums', [
            'slug' => 'New',
            'name' => 'New',
        ]);
    }

    #[Test]
    public function doesNotCreateCategory_ifCategoryExists(): void
    {
        factory(Forum::class)->create([
            'name' => 'Previous one',
            'slug' => 'slug',
        ]);
        $this->driver->seedCategoryIfNotExists('slug');
        $this->laravel->assertDatabaseRecordNotExists('forums', [
            'name' => 'slug', // if it was just created, it will have the same slug
            'slug' => 'slug',
        ]);
    }
}
