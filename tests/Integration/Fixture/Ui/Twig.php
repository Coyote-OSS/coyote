<?php
namespace Tests\Integration\Fixture\Ui;

use Dom;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\FileViewFinder;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use TwigBridge;

trait Twig {
    private function renderTwigTemplate(string $sourceCode, array $data): Dom\HTMLDocument {
        $html = $this->renderTemplateWithPaths($sourceCode, $data, [
            $this->realPath(__DIR__ . '/../../../../resources/feature/components/'),
        ]);
        return Dom\HTMLDocument::createFromString($html);
    }

    private function renderTemplateWithPaths(string $sourceCode, array $data, array $includePaths): string {
        $filesystem = new Filesystem();
        $finder = new FileViewFinder($filesystem, $includePaths, ['twig']);
        $twig = new Environment(new ChainLoader([
            new ArrayLoader(['renderedTemplate' => $sourceCode]),
            new TwigBridge\Twig\Loader($filesystem, $finder, 'twig'),
        ]));
        return $twig->render('renderedTemplate', $data);
    }

    private function realPath(string $path): string {
        $realPath = \realPath($path);
        if ($realPath === false) {
            throw new \Exception('Failed to include twig path.');
        }
        return $realPath;
    }
}
