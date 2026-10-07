<?php
namespace Tests\Integration\Web\Harness;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Integration\Fixture\Filesystem\TemporaryDirectory;
use Tests\Integration\Fixture\Laravel\TestModeDriver;
use Tests\Legacy\Integration\BaseFixture\Server;
use Web\Harness\HarnessBuild;
use Web\Harness\HarnessServiceProvider;

#[CoversClass(HarnessServiceProvider::class)]
class HarnessServiceProviderTest extends TestCase {
    use Server\Laravel\Application;

    private TestModeDriver $testMode;
    private TemporaryDirectory $buildDirectory;

    #[Before]
    public function givenAcceptanceTestMode(): void {
        $this->testMode = new TestModeDriver($this->laravel, testMode:true);
    }

    #[Before]
    public function givenHarnessBuild(): void {
        $this->buildDirectory = new TemporaryDirectory('harness-build');
        $this->laravel->app->instance(HarnessBuild::class,
            new HarnessBuild($this->buildDirectory->path));
    }

    #[After]
    public function removeHarnessBuild(): void {
        $this->buildDirectory->remove();
    }

    #[Test]
    public function openingHarness_isNotAvailableOutsideAcceptanceTests(): void {
        // given the harness is built
        $this->buildDirectory->putFile('index.html', '<div id="app"></div>');
        // and the application is not running acceptance tests
        $this->testMode->setProductionMode();
        // when I open the harness
        $response = $this->laravel->get('/harness');
        // then the harness is not found
        $response->assertNotFound();
    }

    #[Test]
    public function openingHarness_respondsWithBuiltClientPage(): void {
        // given the harness is built
        $this->buildDirectory->putFile('index.html', '<div id="app"></div>');
        // when I open the harness
        $response = $this->laravel->get('/harness?view=forum-job-offers');
        // then the built client page is returned
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
        $this->assertSame('<div id="app"></div>', $this->content($response));
    }

    #[Test]
    public function fetchingHarnessAsset_respondsWithBuiltAsset(): void {
        // given the harness is built with a script
        $this->buildDirectory->putFile('assets/main-a1b2.js', 'mount();');
        // when I fetch the script
        $response = $this->laravel->get('/harness/assets/main-a1b2.js');
        // then the built script is returned
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/javascript; charset=utf-8');
        $this->assertSame('mount();', $this->content($response));
    }

    #[Test]
    public function fetchingHarnessAsset_isNotAvailableOutsideAcceptanceTests(): void {
        // given the harness is built with a script
        $this->buildDirectory->putFile('assets/main-a1b2.js', 'mount();');
        // and the application is not running acceptance tests
        $this->testMode->setProductionMode();
        // when I fetch the script
        $response = $this->laravel->get('/harness/assets/main-a1b2.js');
        // then the harness is not found
        $response->assertNotFound();
    }

    #[Test]
    public function fetchingMissingHarnessAsset_respondsNotFound(): void {
        // when I fetch a script that was not built
        $response = $this->laravel->get('/harness/assets/missing.js');
        // then the asset is not found
        $response->assertNotFound();
    }

    private function content(TestResponse $response): string {
        return $response->baseResponse->getFile()->getContent();
    }
}
