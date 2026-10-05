<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use Laravel\Dusk;

readonly class BrowserDriver {
    public function __construct(private Dusk\Browser $browser) {}

    public function browser(): Dusk\Browser {
        return $this->browser;
    }

    public function navigate(string $url): void {
        $this->browser->visit($url);
    }

    public function submit(string $button): void {
        $this->browser->waitForReload(fn(Dusk\Browser $browser) => $browser->press($button));
    }

    public function screenshot(string $path): void {
        $directory = \dirname($path);
        if (!\is_dir($directory)) {
            \mkdir($directory, 0777, true);
        }
        $this->browser->driver->takeScreenshot($path);
    }

    public function clearState(): void {
        // The session outlives the scenario, so state that
        // the next scenario must not inherit is cleared instead.
        $this->browser->driver->manage()->deleteAllCookies();
        if (\str_starts_with($this->browser->driver->getCurrentURL(), Dusk\Browser::$baseUrl)) {
            $this->browser->driver->executeScript('localStorage.clear(); sessionStorage.clear();');
        }
    }

    public function resizeViewport(int $width, int $height): void {
        $this->browser->resize($width, $height);
    }
}
