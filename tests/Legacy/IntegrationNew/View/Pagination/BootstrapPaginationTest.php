<?php
namespace Tests\Legacy\IntegrationNew\View\Pagination;

use Coyote\Domain\Html;
use Coyote\Domain\View\Pagination\BootstrapPagination;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Neon\View\ViewDom;
use Tests\Legacy\IntegrationNew\BaseFixture\Neon\View\ViewDomElement;
use function array_search;
use function explode;

class BootstrapPaginationTest extends TestCase
{
    /**
     * @test
     */
    public function emptyPagination(): void
    {
        $this->assertControls(
            new BootstrapPagination(1, 1, 0),
            []);
    }

    /**
     * @test
     */
    public function middlePage(): void
    {
        $this->assertControls(
            new BootstrapPagination(2, 10, 25),
            ['«', '1', '2', '3', '»']);
    }

    /**
     * @test
     */
    public function noPreviousPage(): void
    {
        $this->assertControls(
            new BootstrapPagination(1, 10, 20),
            ['1', '2', '»']);
    }

    /**
     * @test
     */
    public function noNextPage(): void
    {
        $this->assertControls(
            new BootstrapPagination(2, 10, 20),
            ['«', '1', '2']);
    }

    /**
     * @test
     */
    public function underflowPageControls(): void
    {
        $this->assertControls(new BootstrapPagination(3, 1, 2), ['«', '1', '2']);
        $this->assertControls(new BootstrapPagination(2, 1, 2), ['«', '1', '2']);
        $this->assertControls(new BootstrapPagination(1, 1, 2), ['1', '2', '»']);
        $this->assertControls(new BootstrapPagination(0, 1, 2), ['1', '2', '»']);
        $this->assertControls(new BootstrapPagination(-1, 1, 2), ['1', '2', '»']);
        $this->assertControls(new BootstrapPagination(-2, 1, 2), ['1', '2', '»']);
    }

    private function assertControls(BootstrapPagination $pagination, array $expectedControls): void
    {
        $dom = new ViewDom($pagination);
        $this->assertSame($expectedControls, $dom->findStrings('//ul/li/*/text()'));
    }

    /**
     * @test
     */
    public function onFirstPageFirstButtonIsActive(): void
    {
        $this->assertContains(
            'active',
            $this->controlCssClasses(
                new BootstrapPagination(1, 10, 11),
                '1'));
    }

    /**
     * @test
     */
    public function onFirstPageSecondButtonIsNotActive(): void
    {
        $this->assertNotContains(
            'active',
            $this->controlCssClasses(
                new BootstrapPagination(1, 10, 11),
                '2'));
    }

    /**
     * @test
     */
    public function onSecondPageSecondButtonIsActive(): void
    {
        $this->assertContains(
            'active',
            $this->controlCssClasses(
                new BootstrapPagination(2, 10, 11),
                '2'));
    }

    /**
     * @test
     */
    public function underflowPageActive(): void
    {
        $this->assertContains(
            'active',
            $this->controlCssClasses(
                new BootstrapPagination(-1, 10, 11),
                '1'));
    }

    /**
     * @test
     */
    public function collapseDisabled(): void
    {
        $this->assertContains(
            'disabled',
            $this->controlCssClasses(
                new BootstrapPagination(13, 10, 130),
                '...'));
    }

    /**
     * @test
     */
    public function linkButton(): void
    {
        $pagination = new BootstrapPagination(1, 10, 30);
        $this->assertSame('?page=2', $this->controlLink($pagination, '2'));
        $this->assertSame('?page=3', $this->controlLink($pagination, '3'));
    }

    /**
     * @test
     */
    public function linkCollapseDisabled(): void
    {
        $pagination = new BootstrapPagination(1, 10, 130);
        $this->assertNull(
            $this->controlLink($pagination, '...'));
    }

    /**
     * @test
     */
    public function linkCollapseNotLink(): void
    {
        $pagination = new BootstrapPagination(1, 10, 130);
        $this->assertSame(
            'span',
            $this->control($pagination, '...')
                ->firstChild()
                ->tagName());
    }

    /**
     * @test
     */
    public function linkActivePageNotLink(): void
    {
        $pagination = new BootstrapPagination(1, 10, 20);
        $this->assertSame(
            'span',
            $this->control($pagination, '1')
                ->firstChild()
                ->tagName());
    }

    /**
     * @test
     */
    public function nextPageLink(): void
    {
        $pagination = new BootstrapPagination(3, 10, 50);
        $this->assertSame('?page=4',
            $this->controlLink($pagination, '»'));
    }

    /**
     * @test
     */
    public function previousPageLink(): void
    {
        $pagination = new BootstrapPagination(3, 10, 50);
        $this->assertSame('?page=2',
            $this->controlLink($pagination, '«'));
    }

    /**
     * @test
     */
    public function additionalQueryParams(): void
    {
        $pagination = new BootstrapPagination(1, 1, 2, ['foo' => 'bar']);
        $this->assertSame('?page=2&foo=bar',
            $this->controlLink($pagination, '2'));
    }

    private function controlCssClasses(BootstrapPagination $pagination, string $control): array
    {
        $classAttribute = $this->control($pagination, $control)->attribute('class');
        return explode(' ', $classAttribute);
    }

    private function control(BootstrapPagination $pagination, string $controlValue): ViewDomElement
    {
        $controlIndex = array_search($controlValue, $this->controlValues($pagination));
        $dom = new ViewDom($pagination);
        $xPathRetardedIndex = $controlIndex + 1;
        return $dom->find("//ul/li[$xPathRetardedIndex]");
    }

    private function controlValues(Html $html): array
    {
        $dom = new ViewDom($html);
        return $dom->findStrings("//ul/li/*/text()");
    }

    private function controlLink(BootstrapPagination $pagination, string $controlValue): ?string
    {
        return $this->control($pagination, $controlValue)
            ->firstChild()
            ->attribute('href');
    }
}
