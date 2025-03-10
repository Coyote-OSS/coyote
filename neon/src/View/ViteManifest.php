<?php
namespace Neon\View;

readonly class ViteManifest
{
    private array $manifest;

    public function __construct(string $viteRootPath)
    {
        $this->manifest = $this->readManifest(
            \rTrim($viteRootPath, '/') . '/public/manifest.json',
        );
    }

    private function readManifest(string $filepath): array
    {
        return json_decode(\file_get_contents($filepath), true);
    }

    public function scriptUrl(): string
    {
        return $this->manifest['src/main.ts']['file'];
    }

    public function styleUrl(): string
    {
        return $this->manifest['src/main.ts']['css'][0];
    }

    public function fontAwesomeSolidUrl(): string
    {
        return $this->manifest['src/dom/internal/fa-solid-900.woff2']['file'];
    }

    public function fontAwesomeLightUrl(): string
    {
        return $this->manifest['src/dom/internal/fa-light-300.woff2']['file'];
    }
}
