<?php
namespace Tests\Legacy\Integration\BaseFixture\View;

class JavaScriptFixture
{
    private ViewDom $dom;

    public function __construct(string $html)
    {
        $this->dom = new ViewDom($html);
    }

    /**
     * @return Script[]
     */
    public function scriptDeclarations(): array
    {
        $declarations = [];
        foreach ($this->scriptTags() as $script) {
            if (!empty($script->content())) {
                $declarations[] = $script;
            }
        }
        return $declarations;
    }

    private function scriptTags(): iterable
    {
        foreach ($this->dom->elements('/html/body//script') as $scriptElement) {
            yield new Script($scriptElement);
        }
    }
}
