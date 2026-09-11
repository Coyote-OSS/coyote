<?php
namespace Tests\Legacy\Integration\View\Pagination;

use Coyote\Domain\View\Pagination\PageButtons;
use PHPUnit\Framework\TestCase;

class PageButtonsTest extends TestCase
{
    /**
     * @test
     */
    public function noItems(): void
    {
        $this->assertSame(1, $this->lastPageOf(0, 1));
    }

    /**
     * @test
     */
    public function firstItem(): void
    {
        $this->assertSame(1, $this->lastPageOf(1, 1));
    }

    /**
     * @test
     */
    public function secondItem(): void
    {
        $this->assertSame(2, $this->lastPageOf(2, 1));
    }

    /**
     * @test
     */
    public function bigPageFirstItem(): void
    {
        $this->assertSame(1, $this->lastPageOf(1, 2));
    }

    /**
     * @test
     */
    public function bigPageSecondItem(): void
    {
        $this->assertSame(1, $this->lastPageOf(2, 2));
    }

    /**
     * @test
     */
    public function bigPageThirdItem(): void
    {
        $this->assertSame(2, $this->lastPageOf(3, 2));
    }

    /**
     * @test
     */
    public function bigPageFifthItem(): void
    {
        $this->assertSame(3, $this->lastPageOf(5, 2));
    }

    private function lastPageOf(int $total, int $pageSize): int
    {
        $buttons = new PageButtons(1, $pageSize, $total);
        return $buttons->lastPage();
    }

    /**
     * @test
     */
    public function negativeTotal(): void
    {
        // then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Negative total: -1');
        // when
        new PageButtons(1, 1, -1);
    }

    /**
     * @test
     */
    public function zeroPageSize(): void
    {
        // then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid page size: 0');
        // when
        new PageButtons(1, 0, 1);
    }

    /**
     * @test
     */
    public function negativePageSize(): void
    {
        // then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid page size: -1');
        // when
        new PageButtons(1, -1, 0);
    }

    /**
     * @test
     */
    public function totalBiggerThanSizeIsTwoPages(): void
    {
        $pages = new PageButtons(1, 2, 3);
        $this->assertSame([1, 2], $pages->buttons());
    }

    /**
     * @test
     */
    public function totalMoreThanTwiceIsThreePages(): void
    {
        $pages = new PageButtons(1, 5, 11);
        $this->assertSame([1, 2, 3], $pages->buttons());
    }

    /**
     * @test
     */
    public function totalMoreThanTwiceLastPageIsThird(): void
    {
        $pages = new PageButtons(1, 5, 11);
        $this->assertSame(3, $pages->lastPage());
    }

    /**
     * @test
     */
    public function firstPageHasNoPrevious(): void
    {
        $pages = new PageButtons(1, 1, 0);
        $this->assertFalse($pages->hasPrevious());
    }

    /**
     * @test
     */
    public function secondPageHasPrevious(): void
    {
        $pages = new PageButtons(2, 1, 2);
        $this->assertTrue($pages->hasPrevious());
    }

    /**
     * @test
     */
    public function currentPageFirst(): void
    {
        $this->assertSame(1, (new PageButtons(1, 10, 20))->currentPage());
    }

    /**
     * @test
     */
    public function currentPageOverflow(): void
    {
        $this->assertSame(20, (new PageButtons(20, 5, 11))->currentPage());
    }

    /**
     * @test
     */
    public function smallNoCollapse(): void
    {
        $this->assertButtons(
            new PageButtons(6, 10, 110),
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);
    }

    /**
     * @test
     */
    public function mediumCollapseFirstPage(): void
    {
        $this->assertButtons(
            new PageButtons(1, 10, 120),
            [1, 2, 3, 4, 5, 6, 7, 8, '...', 11, 12]);
    }

    /**
     * @test
     */
    public function mediumCollapseMiddlePageLeft(): void
    {
        $this->assertButtons(
            new PageButtons(6, 10, 120),
            [1, 2, 3, 4, 5, 6, 7, 8, '...', 11, 12]);
    }

    /**
     * @test
     */
    public function mediumCollapseMiddlePageRight(): void
    {
        $this->assertButtons(
            new PageButtons(7, 10, 120),
            [1, 2, '...', 5, 6, 7, 8, 9, 10, 11, 12]);
    }

    /**
     * @test
     */
    public function mediumCollapseLastPage(): void
    {
        $this->assertButtons(
            new PageButtons(12, 10, 120),
            [1, 2, '...', 5, 6, 7, 8, 9, 10, 11, 12]);
    }

    /**
     * @test
     */
    public function largeCollapseFirstPage(): void
    {
        $this->assertButtons(
            new PageButtons(1, 10, 130),
            [1, 2, 3, 4, 5, 6, 7, 8, '...', 12, 13]);
    }

    /**
     * @test
     */
    public function largeCollapseMiddlePage(): void
    {
        $this->assertButtons(
            new PageButtons(7, 10, 130),
            [1, 2, '...', 5, 6, 7, 8, 9, '...', 12, 13]);
    }

    /**
     * @test
     */
    public function largeCollapseLastPage(): void
    {
        $this->assertButtons(
            new PageButtons(13, 10, 130),
            [1, 2, '...', 6, 7, 8, 9, 10, 11, 12, 13]);
    }

    private function assertButtons(PageButtons $pagination, array $expectedControls): void
    {
        $this->assertSame($expectedControls, $pagination->buttons());
    }
}
