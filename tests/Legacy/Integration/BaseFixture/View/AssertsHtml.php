<?php
namespace Tests\Legacy\Integration\BaseFixture\View;

use PHPUnit\Framework\Assert;

trait AssertsHtml
{
    function assertTextNodes(array $expectedNodes, string $html, string $xPath): void
    {
        Assert::assertSame($expectedNodes, $this->textNodes($html, $xPath));
    }

    function textNode(string $html, string $xPath): string
    {
        $nodes = $this->textNodes($html, $xPath);
        if (count($nodes) === 1) {
            return $nodes[0];
        }
        throw new \Exception("Failed to find a distinct node: $xPath");
    }

    function textNodes(string $html, string $xPath): array
    {
        $path = new \DOMXPath($this->document($html));
        $textNodes = [];
        foreach ($path->query($xPath) as $item) {
            $textNodes[] = \trim($item->textContent);
        }
        return $textNodes;
    }

    private function document(string $html): \DOMDocument
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        return $dom;
    }
}
