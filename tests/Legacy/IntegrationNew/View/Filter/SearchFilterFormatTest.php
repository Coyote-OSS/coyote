<?php
namespace Tests\Legacy\IntegrationNew\View\Filter;

use Coyote\Domain\Administrator\UserMaterial\List\Store\SearchFilter;
use Coyote\Domain\Administrator\UserMaterial\List\Store\SearchFilterType;
use Coyote\Domain\View\Filter\SearchFilterFormat;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SearchFilterFormatTest extends TestCase {
    #[Test]
    public function initiallyValuesAreNull(): void {
        $filter = $this->parse('');
        $this->assertNull($filter->reported);
        $this->assertNull($filter->reportOpen);
        $this->assertNull($filter->deleted);
        $this->assertNull($filter->authorId);
    }

    #[Test]
    public function initiallyTypeIsPost(): void {
        $this->assertSame(SearchFilterType::Post, $this->parse('')->type);
    }

    #[Test]
    public function filterByTypePost() {
        $this->assertSame(SearchFilterType::Post,
            $this->parse('type:post')->type);
    }

    #[Test]
    public function filterByTypeComment() {
        $this->assertSame(SearchFilterType::PostComment,
            $this->parse('type:comment')->type);
    }

    #[Test]
    public function filterByTypeMicroblog() {
        $this->assertSame(SearchFilterType::Blog,
            $this->parse('type:microblog')->type);
    }

    #[Test]
    public function defaultsToPost_givenInvalidType() {
        $this->assertSame(SearchFilterType::Post,
            $this->parse('type:other')->type);
    }

    #[Test]
    public function returnLaterType_givenMultipleTypes(): void {
        $this->assertSame(SearchFilterType::Blog,
            $this->parse('type:post type:microblog')->type);
    }

    #[Test]
    public function filterByReported(): void {
        $this->assertTrue($this->parse('is:reported')->reported);
    }

    #[Test]
    public function filterByNotReported(): void {
        $this->assertFalse($this->parse('not:reported')->reported);
    }

    #[Test]
    public function filterByReportOpen() {
        $this->assertTrue($this->parse('is:open')->reportOpen);
    }

    #[Test]
    public function filterByReportNotOpen() {
        $this->assertFalse($this->parse('not:open')->reportOpen);
    }

    #[Test]
    public function filterByDeleted(): void {
        $this->assertTrue($this->parse('is:deleted')->deleted);
    }

    #[Test]
    public function filterByNotDeleted(): void {
        $this->assertFalse($this->parse('not:deleted')->deleted);
    }

    #[Test]
    public function filterByAuthor(): void {
        $this->assertSame(123, $this->parse('author:123')->authorId);
    }

    #[Test]
    public function filterByAuthorZero(): void {
        $this->assertSame(0, $this->parse('author:0')->authorId);
        $this->assertSame(0, $this->parse('author:00')->authorId);
    }

    #[Test]
    public function authorEmptyString(): void {
        $this->assertNull($this->parse('author:')->authorId);
    }

    #[Test]
    public function danglingWord(): void {
        $this->assertNull($this->parse('author')->authorId);
    }

    #[Test]
    public function nonInteger(): void {
        $this->assertNull($this->parse('author:abc')->authorId);
    }

    public function parse(string $format): SearchFilter {
        return new SearchFilterFormat($format)->toSearchFilter();
    }
}
