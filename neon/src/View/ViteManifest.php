<?php
namespace Neon\View;

use function Illuminate\Filesystem\join_paths;

readonly class ViteManifest
{
    private array $manifest;

    public function __construct(string $viteRootPath)
    {
        $this->manifest = $this->readManifest(
            join_paths($viteRootPath, './public/manifest.json'),
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
        return $this->manifest['style.css']['file'];
    }
}
