<?php
namespace Tests\Legacy\IntegrationNew\View\Filter;

use Coyote\Domain\Administrator\UserMaterial\List\Store\SearchFilter;
use Coyote\Domain\Administrator\UserMaterial\List\Store\SearchFilterType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SearchFilterTest extends TestCase {
    #[Test]
    public function defaultFiltersReturnTypePost(): void {
        $this->assertSame('type:post', $this->filter(SearchFilterType::Post));
    }

    #[Test]
    public function typePostComment_returnsTypeComment(): void {
        $this->assertSame('type:comment', $this->filter(SearchFilterType::PostComment));
    }

    #[Test]
    public function typePostBlog_returnsTypeMicroblog(): void {
        $this->assertSame('type:microblog', $this->filter(SearchFilterType::Blog));
    }

    #[Test]
    public function deleted(): void {
        $this->assertSame('type:post is:deleted', $this->filter(deleted:true));
    }

    #[Test]
    public function notDeleted(): void {
        $this->assertSame('type:post not:deleted', $this->filter(deleted:false));
    }

    #[Test]
    public function author(): void {
        $this->assertSame('type:post author:123', $this->filter(authorId:123));
    }

    #[Test]
    public function author0(): void {
        $this->assertSame('type:post author:0', $this->filter(authorId:0));
    }

    #[Test]
    public function reported(): void {
        $this->assertSame('type:post is:reported', $this->filter(reported:true));
    }

    #[Test]
    public function notReported(): void {
        $this->assertSame('type:post not:reported', $this->filter(reported:false));
    }

    #[Test]
    public function reportOpen(): void {
        $this->assertSame('type:post report:open', $this->filter(reportOpen:true));
    }

    #[Test]
    public function reportClosed(): void {
        $this->assertSame('type:post report:closed', $this->filter(reportOpen:false));
    }

    #[Test]
    public function deletionIsLaterThanReport(): void {
        $this->assertSame('type:post is:reported report:open is:deleted',
            $this->filter(deleted:true, reported:true, reportOpen:true));
    }

    private function filter(
        SearchFilterType $type = SearchFilterType::Post,
        ?bool            $deleted = null,
        ?bool            $reported = null,
        ?bool            $reportOpen = null,
        ?int             $authorId = null,
    ): string {
        return new SearchFilter(
            $type,
            $deleted,
            $reported,
            $reportOpen,
            $authorId)->toString();
    }
}
