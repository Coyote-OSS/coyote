<?php
namespace Tests\Legacy\Integration\Settings;

use Coyote\Services\Guest;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture;

class Test extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use BaseFixture\Server\Http;

    public function test()
    {
        $this->laravel->post('/User/Settings/Ajax', [
            'foo.bar'     => 'cat',
            'lorem_ipsum' => 'dolor',
        ]);
        $this->assertSettings([
            'foo.bar'     => 'cat',
            'lorem.ipsum' => 'dolor',
        ]);
    }

    private function assertSettings(array $expected): void
    {
        /** @var Guest $app */
        $app = $this->laravel->app->get(Guest::class);
        $this->assertSame($expected, $app->getSettings());
    }
}
