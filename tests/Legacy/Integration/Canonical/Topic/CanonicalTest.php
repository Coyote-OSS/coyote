<?php
namespace Tests\Legacy\Integration\Canonical\Topic;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\Canonical;

class CanonicalTest extends TestCase
{
    use Canonical\Fixture\Assertion,
        Canonical\Topic\Fixture\Models;

    /**
     * @test
     */
    public function canonical()
    {
        $topicId = $this->newForumTopic('apple-category', 'Apple thread');
        $this->assertNoRedirectGet("/Forum/apple-category/$topicId-apple_thread");
    }

    /**
     * @test
     */
    public function replaceTopicSlug()
    {
        $id = $this->newForumTopic('kiwi-category', 'Kiwi thread');
        $this->assertRedirectGet(
            "/Forum/kiwi-category/$id-invalid-slug",
            "/Forum/kiwi-category/$id-kiwi_thread");
    }

    /**
     * @test
     */
    public function noTopicSlug()
    {
        $id = $this->newForumTopic('kiwi-category', 'Kiwi thread');
        $this->assertRedirectGet(
            "/Forum/kiwi-category/$id",
            "/Forum/kiwi-category/$id-kiwi_thread");
    }

    /**
     * @test
     */
    public function replaceForumSlug()
    {
        $this->newForumSlug('apple-category');
        $topicId = $this->newForumTopic('banana-category', 'Banana thread');
        $this->assertRedirectGet(
            "/Forum/apple-category/$topicId-thread",
            "/Forum/banana-category/$topicId-banana_thread");
    }

    /**
     * @test
     */
    public function maintainQueryParams()
    {
        $topicId = $this->newForumTopic('pear-category', 'Pear thread');
        $this->assertRedirectGet(
            "/Forum/pear-category/$topicId-invalid-slug?query=param",
            "/Forum/pear-category/$topicId-pear_thread?query=param");
    }

    /**
     * @test
     */
    public function systemQueryParams()
    {
        $topicId = $this->newForumTopic('papaya-category', 'Papaya thread');
        $this->assertRedirectGet(
            "/Forum/papaya-category/$topicId-invalid-slug?forum=invalid",
            "/Forum/papaya-category/$topicId-papaya_thread");
    }

    #[Test]
    public function topicTitleEmptySlug_noDash_noRouteSlug(): void
    {
        $topicId = $this->newForumTopic('coyote', '注意');
        $this->assertNoRedirectGet("/Forum/coyote/$topicId");
    }

    #[Test]
    public function topicTitleEmptySlug_dash_noRouteSlug(): void
    {
        $topicId = $this->newForumTopic('coyote', '注意');
        $this->assertNoRedirectGet("/Forum/coyote/$topicId-");
    }

    #[Test]
    public function topicTitleEmptySlug_dash_mismatchedRouteSlug(): void
    {
        $topicId = $this->newForumTopic('coyote', '注意');
        $this->assertRedirectGet(
            "/Forum/coyote/$topicId-foo",
            "/Forum/coyote/$topicId-");
    }
}
