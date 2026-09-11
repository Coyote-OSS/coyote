<?php
namespace Tests\Legacy\Integration\BaseFixture\View;

use DOMDocument;
use DOMXPath;

class ViewDom
{
    private DOMDocument $document;

    public function __construct(private string $html)
    {
        $this->document = new DOMDocument();
        @$this->document->loadHTML($this->html);
    }

    public function elements(string $xPath): iterable
    {
        $xpath = new DomXPath($this->document);
        return $xpath->query($xPath);
    }
}
