<?php
namespace Neon2\Web;

readonly class ViteManifest {
    private array $manifest;

    public function __construct(private string $viteRootPath) {
        $this->manifest = json_decode($this->fileContent('manifest.json'), true);
    }

    public function scriptUrl(): string {
        return $this->manifest['src/Infrastructure/Main/main.ts']['file'];
    }

    public function styleUrl(): string {
        return $this->manifest['src/Infrastructure/Main/main.ts']['css'][0];
    }

    public function fontAwesomeLightUrl(): string {
        return $this->manifest['src/view/ui/icons/internal/fa-light-300.woff2']['file'];
    }

    public function fontAwesomeRegularUrl(): string {
        return $this->manifest['src/view/ui/icons/internal/fa-regular-400.woff2']['file'];
    }

    public function fontAwesomeSolidUrl(): string {
        return $this->manifest['src/view/ui/icons/internal/fa-solid-900.woff2']['file'];
    }

    private function fileContent(string $distPath): string {
        return \file_get_contents(\rTrim($this->viteRootPath, '/') . "/dist/$distPath");
    }

    public function faviconUrl(): string {
        return 'favicon.svg';
    }
}
