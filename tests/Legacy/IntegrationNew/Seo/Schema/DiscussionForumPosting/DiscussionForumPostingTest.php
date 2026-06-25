<?php
namespace Tests\Legacy\IntegrationNew\Seo\Schema\DiscussionForumPosting;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;
use Tests\Legacy\IntegrationNew\BaseFixture\Constraint\ArrayStructure;
use Tests\Legacy\IntegrationNew\Seo;

/**
 * @see https://developers.google.com/search/docs/appearance/structured-data/discussion-forum
 */
class DiscussionForumPostingTest extends TestCase {
    use Seo\Schema\DiscussionForumPosting\Fixture\Schema,
        Seo\Schema\DiscussionForumPosting\Fixture\SystemDatabase,
        BaseFixture\Server\RelativeUri;

    #[Test]
    #[TestWith(['@id'])]
    #[TestWith(['url'])]
    public function canonical(string $field) {
        [$schema, $topicId] = $this->schemaTopicInForum('Banana topic', 'apple-forum');
        $this->assertThat(
            $schema[$field],
            $this->relativeUri("/Forum/apple-forum/$topicId-banana_topic"));
    }

    #[Test]
    public function headline() {
        $schema = $this->schemaTopicTitle('Banana topic');
        $this->assertSame('Banana topic', $schema['headline']);
    }

    #[Test]
    public function contentMarkdown() {
        $schema = $this->schemaTopicContent('Lorem *ipsum*.');
        $this->assertSame('Lorem ipsum.', $schema['text']);
    }

    #[Test]
    public function contentWhiteSpace() {
        $schema = $this->schemaTopicContent("Lorem\n\n\nipsum");
        $this->assertSame('Lorem ipsum', $schema['text']);
    }

    #[Test]
    public function contentHtml() {
        $schema = $this->schemaTopicContent('Lorem <b>ipsum</b> &copy;.');
        $this->assertSame('Lorem ipsum ©.', $schema['text']);
    }

    #[Test]
    public function contentHtmlEntity() {
        $schema = $this->schemaTopicContent('&lt;Lorem&gt;');
        $this->assertSame('<Lorem>', $schema['text']);
    }

    #[Test]
    public function statistics() {
        $schema = $this->schemaForumStatistic(replies:7, likes:5, views:13);
        $this->assertThat(
            $schema['interactionStatistic'],
            $this->logicalAnd(
                $this->interaction('ViewAction', 13),
                $this->interaction('LikeAction', 5),
                $this->interaction('CommentAction', 7),
            ));
    }

    private function interaction(string $type, int $count): Constraint {
        return $this->containsEqual([
            '@type'                => 'InteractionCounter',
            'interactionType'      => "https://schema.org/$type",
            'userInteractionCount' => $count,
        ]);
    }

    #[Test]
    public function contentLong() {
        $text = \str_repeat('Lorem ipsum', 10);
        $schema = $this->schemaTopicContent($text);
        $this->assertSame($text, $schema['text']);
    }

    #[Test]
    public function authorUser() {
        $schema = $this->postingSchema($this->newTopicAuthorUsername('mark'));
        $this->assertThat(
            $schema['author'],
            new ArrayStructure([
                '@type' => 'Person',
                'name'  => 'mark',
            ]));
    }

    #[Test]
    public function authorLegacyGuest() {
        $schema = $this->postingSchema($this->newTopicAuthorLegacyGuest('john'));
        $this->assertThat(
            $schema['author'],
            $this->identicalTo(['@type' => 'Person', 'name' => 'john']));
    }

    #[Test]
    public function authorUserUrl() {
        $user = $this->newUser();
        $schema = $this->postingSchema($this->newTopicAuthorUser($user));
        $this->assertThat(
            $schema['author']['url'],
            $this->relativeUri("/Profile/$user->id"));
    }

    #[Test]
    public function datePublished() {
        $this->systemDatabaseTimezone('America/Los_Angeles');
        $schema = $this->schemaTopicCreatedAt('2016-01-23 11:53:20', timezone:'Europe/Stockholm');
        $this->assertThat($schema['datePublished'],
            $this->identicalTo('2016-01-23T02:53:20-08:00'));
    }
}
