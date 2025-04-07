<?php
namespace Neon2\Web;

readonly class ViteManifest
{
    private array $manifest;

    public function __construct(private string $viteRootPath)
    {
        $this->manifest = json_decode($this->fileContent('manifest.json'), true);
    }

    public function scriptUrl(): string
    {
        return $this->manifest['src/main.ts']['file'];
    }

    private function fileContent(string $distPath): string
    {
        return \file_get_contents(\rTrim($this->viteRootPath, '/') . "/dist/$distPath");
    }
}
