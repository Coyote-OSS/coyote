<?php
namespace Tests\Legacy\IntegrationNew\Seo\Post;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;

class PostTest extends TestCase {
    use BaseFixture\Forum\Store;

    #[Test]
    function canonicalLink() {
        [$topicId, $postId] = $this->newPostInTopic('papaya-slug', 'Papaya topic');
        $this->assertRendered(
            $this->markdownLink("/Forum/$postId"),
            $this->htmlLink("/Forum/papaya-slug/$topicId-papaya_topic?p=$postId#id$postId"));
    }

    #[Test]
    function pathMissingId() {
        $this->assertLinkUnchanged('/Forum');
    }

    #[Test]
    function pathMissingIdTrailingSlash() {
        $this->assertLinkUnchanged('/Forum/');
    }

    #[Test]
    function pathIdNonNumeric() {
        $this->assertLinkUnchanged('/Forum/123a');
    }

    #[Test]
    function pathIdNonExistent() {
        $this->assertLinkUnchanged('/Forum/99999999');
    }

    #[Test]
    function pathSegmentSuperfluous() {
        $id = $this->newPost();
        $this->assertLinkUnchanged("/Forum/Forum/$id");
    }

    #[Test]
    function pathSegmentMissing() {
        $id = $this->newPost();
        $this->assertLinkUnchanged("/$id");
    }

    #[Test]
    function pathSegmentIncorrectBase() {
        $id = $this->newPost();
        $this->assertLinkUnchanged("/other/$id");
    }

    #[Test]
    function pathMissingLeadingSlash() {
        $id = $this->newPost();
        $this->assertLinkUnchanged("Forum/$id");
    }

    #[Test]
    function malformedUrl() {
        $this->assertRendered(
            $this->markdownLink(':'),
            $this->htmlLink('%3A'));
    }

    #[Test]
    function acceptsQueryString() {
        [$topicId, $postId] = $this->newPostInTopic('guava-slug', 'Guava topic');
        $this->assertRendered(
            $this->markdownLink("/Forum/$postId?foo=bar"),
            $this->htmlLink("/Forum/guava-slug/$topicId-guava_topic?p=$postId#id$postId"));
    }

    private function assertLinkUnchanged(string $uri): void {
        $this->assertRendered(
            $this->markdownLink($uri),
            $this->htmlLink($uri));
    }

    private function assertRendered(string $markdown, string $html): void {
        $this->assertSame(
            $html,
            \rTrim($this->rendered($markdown), "\n"));
    }

    private function rendered(string $markdown): string {
        return (new Post(['text' => $markdown]))->html;
    }

    private function newPostInTopic(?string $forumSlug = null, ?string $topicTitle = null): array {
        $topic = $this->storeThread(
            new Forum(['slug' => $forumSlug]),
            new Topic(['title' => $topicTitle]));
        return [$topic->id, $topic->first_post_id];
    }

    private function newPost(?string $forumSlug = null, ?string $topicTitle = null): string {
        $topic = $this->storeThread(
            new Forum(['slug' => $forumSlug]),
            new Topic(['title' => $topicTitle]));
        return $topic->first_post_id;
    }

    private function markdownLink(string $uri): string {
        return "[title]($uri)";
    }

    private function htmlLink(string $uri): string {
        return '<p><a href="' . $uri . '" rel="nofollow">title</a></p>';
    }
}
