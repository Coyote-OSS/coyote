<?php
namespace Tests\Legacy\Integration\Topic\Fixture;

use Tests\Legacy\Integration\BaseFixture\View\JavaScriptFixture;

class SpaView
{
    private JavaScriptFixture $view;

    public function __construct(private string $html)
    {
        $this->view = new JavaScriptFixture($this->html);
    }

    public function jsVariables(): array
    {
        $variables = [];
        foreach ($this->javaScriptVariableDeclarations() as $declaration) {
            \preg_match('/var\s+(\w+)\s+=\s+(.+);/', $declaration, $match);
            if ($match) {
                $variables[$match[1]] = \json_decode($match[2], true);
            }
        }
        return $variables;
    }

    private function javaScriptVariableDeclarations(): \Iterator
    {
        foreach ($this->view->scriptDeclarations() as $script) {
            if ($script->type() === 'application/ld+json') {
                continue;
            }
            foreach (explode("\n", $script->content()) as $line) {
                if ($line === '') {
                    continue;
                }
                yield \trim($line);
            }
        }
    }
}
