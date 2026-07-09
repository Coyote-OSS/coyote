<?php
namespace Tests\Legacy\IntegrationNew\Icon;

use Coyote\Domain\Html;
use Coyote\Services\TwigBridge\Extensions\Icon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Runtime\EscaperRuntime;

class TwigExtensionTest extends TestCase {
    #[Test]
    public function iconTickMark(): void {
        $this->assertSame(
            '<i class="fa-light fa-check fa-fw" data-icon="adminTickMark"></i>',
            $this->twig("{{ icon('adminTickMark') }}"));
    }

    #[Test]
    public function iconCrossMark(): void {
        $this->assertSame(
            '<i class="fa-light fa-xmark fa-fw" data-icon="adminCrossMark"></i>',
            $this->twig("{{ icon('adminCrossMark') }}"));
    }

    #[Test]
    public function missingIcon(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('An exception has been thrown during the rendering of a template');
        $this->twig("{{ icon('missing') }}");
    }

    #[Test]
    public function fontAwesomeSpin(): void {
        $this->assertSame(
            '<i class="fa-light fa-xmark fa-fw fa-spin" data-icon="adminCrossMark"></i>',
            $this->twig("{{ icon('adminCrossMark', {spin}) }}"));
    }

    #[Test]
    public function iconTooltip(): void {
        $this->assertSame(
            '<i class="fa-light fa-xmark fa-fw" data-icon="adminCrossMark" title="foo"></i>',
            $this->twig("{{ icon('adminCrossMark', {title:'foo'}) }}"));
    }

    private function twig(string $sourceCode): string {
        $twig = new Environment(new ArrayLoader());
        $twig->getRuntime(EscaperRuntime::class)->addSafeClass(Html::class, ['html']);
        $twig->addExtension(new Icon());
        return $twig->createTemplate($sourceCode)->render();
    }
}
