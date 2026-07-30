<?php
namespace Tests\Legacy\IntegrationNew\Formatting;

use Coyote\Domain\Html;
use Coyote\Services\TwigBridge\Extensions\Formatting;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Runtime\EscaperRuntime;

class TwigExtensionTest extends TestCase {
    #[Test]
    public function numberFormatBelowThousand(): void {
        $this->assertSame(
            '999',
            $this->twig("{{ 999|number_format }}"));
    }

    #[Test]
    public function numberFormatThousands(): void {
        $this->assertSame(
            '12,345',
            $this->twig("{{ 12345|number_format }}"));
    }

    #[Test]
    public function numberFormatMillions(): void {
        $this->assertSame(
            '1,234,567',
            $this->twig("{{ 1234567|number_format }}"));
    }

    private function twig(string $sourceCode): string {
        $twig = new Environment(new ArrayLoader());
        $twig->getRuntime(EscaperRuntime::class)->addSafeClass(Html::class, ['html']);
        $twig->addExtension(new Formatting());
        return $twig->createTemplate($sourceCode)->render();
    }
}
