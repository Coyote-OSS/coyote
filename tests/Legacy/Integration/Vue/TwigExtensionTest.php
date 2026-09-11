<?php
namespace Tests\Legacy\Integration\Vue;

use Coyote\Domain\Html;
use Coyote\Services\TwigBridge\Extensions\Vue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Runtime\EscaperRuntime;

class TwigExtensionTest extends TestCase
{
    #[Test]
    public function booleanTrue(): void
    {
        $this->assertSame(
            'true',
            $this->twig("{{ vueBoolean(value) }}", [
                'value' => true,
            ]));
    }

    #[Test]
    public function booleanFalse(): void
    {
        $this->assertSame(
            'false',
            $this->twig("{{ vueBoolean(value) }}", [
                'value' => false,
            ]));
    }

    private function twig(string $sourceCode, array $values): string
    {
        $twig = new Environment(new ArrayLoader());
        $twig->getRuntime(EscaperRuntime::class)->addSafeClass(Html::class, ['html']);
        $twig->addExtension(new Vue());
        return $twig->createTemplate($sourceCode)->render($values);
    }
}
