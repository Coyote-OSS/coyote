<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Neon\View;

readonly class ViewDom
{
    private \DOMDocument $document;
    private \DOMXPath $xPath;

    public function __construct(string $html)
    {
        $this->document = new \DOMDocument();
        \libxml_use_internal_errors(true);
        $this->document->loadHTML($html);
        \libxml_clear_errors();
        $this->xPath = new \DOMXPath($this->document);
    }

    public function html(string $xPath): string
    {
        return $this->document->saveHTML($this->first($xPath));
    }

    public function exists(string $xPath): bool
    {
        return $this->query($xPath)->count() > 0;
    }

    public function findStrings(string $xPath): array
    {
        $texts = [];
        foreach ($this->query($xPath) as $child) {
            $texts[] = $this->string($child);
        }
        return $texts;
    }

    public function findElementsFlatTexts(string $xPath): array
    {
        $text = [];
        foreach ($this->query($xPath) as $child) {
            if ($child->nodeType === \XML_TEXT_NODE) {
                throw new \Exception("Failed to get element as flat string: received a text node.");
            }
            if ($child->nodeType === \XML_ATTRIBUTE_NODE) {
                throw new \Exception("Failed to get element as flat string: received an attribute node.");
            }
            /** @var \DOMElement $child */
            $text[] = $child->textContent;
        }
        return $text;
    }

    public function findString(string $xPath): string
    {
        return $this->string($this->first($xPath));
    }

    private function query(string $xPath): \DOMNodeList
    {
        $result = $this->xPath->query($xPath);
        if ($result === false) {
            throw new \Exception("Failed to execute malformed xPath: $xPath");
        }
        return $result;
    }

    private function string(\DOMText|\DOMElement|\DOMAttr $node): string
    {
        if ($node->nodeType === \XML_ATTRIBUTE_NODE) {
            return $node->textContent;
        }
        if ($node->nodeType === \XML_TEXT_NODE) {
            return $node->textContent;
        }
        if ($node->nodeType === \XML_CDATA_SECTION_NODE) {
            return $node->textContent;
        }
        throw new \Exception("Failed to get element as string: <$node->tagName>");
    }

    private function firstElement(\DOMNodeList $nodes, string $xPath): \DOMElement|\DOMText|\DOMAttr
    {
        $count = $nodes->count();
        if ($count === 0) {
            throw $this->exception('Failed to find element', $xPath);
        }
        if ($count === 1) {
            return $nodes[0];
        }
        throw $this->exception("Failed to find unique element (found $count)", $xPath);
    }

    private function exception(string $summary, string $xPath): \Exception
    {
        return new \Exception("$summary: $xPath");
    }

    public function docType(): string
    {
        $node = $this->document->getRootNode();
        return \strStr($this->document->saveHTML($node), "\n", true);
    }

    public function find(string $xPath): ViewDomElement
    {
        return new ViewDomElement($this->first($xPath));
    }

    private function first(string $xPath): \DOMAttr|\DOMElement|\DOMText
    {
        return $this->firstElement($this->query($xPath), $xPath);
    }
}
