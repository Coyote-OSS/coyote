<?php
namespace Tests\Legacy\IntegrationNew\Types;

use Coyote\Domain\Html;
use Coyote\Services\TwigBridge\Extensions\Types\NestedAttribute;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Runtime\EscaperRuntime;
use Twig\TwigFilter;

class TwigExtensionTest extends TestCase {
    #[Test]
    public function readsTopLevelProperty(): void {
        $object = (object)['imageUrl' => 'example.png'];
        $this->assertSame(
            'example.png',
            $this->twig("{{ nested_attribute(object, 'imageUrl') }}", $object));
    }

    #[Test]
    public function readsNestedProperty(): void {
        $object = (object)['stats' => (object)['views' => 5]];
        $this->assertSame(
            '5',
            $this->twig("{{ nested_attribute(object, 'stats.views') }}", $object));
    }

    #[Test]
    public function returnsRawIntegerRatherThanMarkup(): void {
        $object = (object)['stats' => (object)['views' => 5]];
        $this->assertSame(
            'int',
            $this->twig("{{ nested_attribute(object, 'stats.views')|debug_type }}", $object));
    }

    #[Test]
    public function returnsNullWhenIntermediateValueIsNull(): void {
        $object = (object)['stats' => null];
        $this->assertSame(
            '',
            $this->twig("{{ nested_attribute(object, 'stats.views') }}", $object));
    }

    #[Test]
    public function returnsNullForUnknownAttribute(): void {
        $object = (object)['stats' => (object)['views' => 5]];
        $this->assertSame(
            '',
            $this->twig("{{ nested_attribute(object, 'doesNotExist') }}", $object));
    }

    private function twig(string $sourceCode, mixed $object): string {
        $twig = new Environment(new ArrayLoader());
        $twig->getRuntime(EscaperRuntime::class)->addSafeClass(Html::class, ['html']);
        $twig->addExtension(new NestedAttribute());
        $twig->addFilter(new TwigFilter('debug_type', get_debug_type(...)));
        return $twig->createTemplate($sourceCode)->render(['object' => $object]);
    }
}
