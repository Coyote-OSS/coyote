<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel;

use Illuminate\Config;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Symfony\Component\HttpFoundation;

class TestCase extends \Illuminate\Foundation\Testing\TestCase {
    use OverridesSymfonyRequest;
    use InteractsWithDatabase;
    use InteractsWithSession;

    /** @var Application */
    public $app;
    /** @var callable */
    public $beforeBoot;

    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    public function createApplication(): Application {
        /** @var Application $app */
        $app = require __DIR__ . '/../../../../../../bootstrap/app.php';
        ($this->beforeBoot)($app);
        /** @var Kernel $kernel */
        $kernel = $app->make(Kernel::class);
        $this->beforeBoot($app);
        $kernel->bootstrap();
        return $app;
    }

    private function beforeBoot(Application $app): void {
        $app->afterBootstrapping(LoadConfiguration::class, $this->disableSentry(...));
    }

    private function disableSentry(Application $app): void {
        /** @var Repository $config */
        $config = $app[Config\Repository::class];
        $config->set('sentry.dsn', '');
    }

    protected function prepareUrlForRequest($uri): string {
        return $uri;
    }

    protected function mapSymfonyRequest(string $uri, HttpFoundation\Request $request): HttpFoundation\Request {
        if (\str_ends_with($uri, '?')) {
            $request->server->set('REQUEST_URI', $request->server->get('REQUEST_URI') . '?');
        }
        return $request;
    }

    public function assertSeeInDatabase(string $table, array $data): void {
        $this->assertDatabaseHas($table, $data);
    }

    public function assertDatabaseRecordNotExists(string $table, array $data): void {
        $this->assertDatabaseMissing($table, $data);
    }

    public function databaseTable(string $table): Builder {
        return $this->getConnection(null, $table)->table($table);
    }
}
