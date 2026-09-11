<?php
namespace Tests\Legacy\Integration\Seo\Schema\BreadcrumbList;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture;
use Tests\Legacy\Integration\BaseFixture\Constraint\ArrayStructure;
use Tests\Legacy\Integration\Seo;

/**
 * @see https://developers.google.com/search/docs/appearance/structured-data/breadcrumb
 */
class Test extends TestCase
{
    use Seo\Schema\BreadcrumbList\Fixture\Schema, BaseFixture\Server\RelativeUri;

    /**
     * @test
     */
    public function forum(): void
    {
        $breadcrumbList = $this->breadcrumbsSchema();
        $this->assertThat(
            $breadcrumbList['itemListElement'][0],
            new ArrayStructure([
                'name'     => 'Forum',
                'position' => 1,
            ]));
    }

    /**
     * @test
     */
    public function category(): void
    {
        $breadcrumbList = $this->categorySchema('Orange category', 'orange-category');
        $this->assertThat(
            $breadcrumbList['itemListElement'][1],
            new ArrayStructure([
                'name'     => 'Orange category',
                'position' => 2,
            ]));
    }

    /**
     * @test
     */
    public function categoryParent(): void
    {
        $breadcrumbList = $this->categoryWithParentSchema('Banana category', 'banana_category');
        $this->assertThat(
            $breadcrumbList['itemListElement'][1],
            new ArrayStructure([
                'name'     => 'Banana category',
                'item'     => $this->relativeUri('/Forum/banana_category'),
                'position' => 2,
            ]));
    }

    /**
     * @test
     */
    public function topic(): void
    {
        $breadcrumbList = $this->topicSchema('Apple topic', 'apple-category');
        $this->assertThat(
            $breadcrumbList['itemListElement'][2],
            new ArrayStructure([
                'name'     => 'Apple topic',
                'position' => 3,
            ]));
    }

    /**
     * @test
     */
    public function categoryLastItemNoUrl(): void
    {
        $breadcrumbList = $this->categorySchemaAny();
        $this->assertThat(
            $breadcrumbList['itemListElement'][1],
            $this->logicalAnd(
                $this->logicalNot($this->arrayHasKey('@id')),
                $this->logicalNot($this->arrayHasKey('item')),
            ));
    }

    /**
     * @test
     */
    public function topicLastItemNoUrl(): void
    {
        $breadcrumbList = $this->topicSchemaAny();
        $this->assertThat(
            $breadcrumbList['itemListElement'][2],
            $this->logicalAnd(
                $this->logicalNot($this->arrayHasKey('@id')),
                $this->logicalNot($this->arrayHasKey('item')),
            ));
    }
}
