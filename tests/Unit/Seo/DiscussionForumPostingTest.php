<?php
namespace Tests\Unit\Seo;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\Seo;
use Tests\Unit\Seo\Fixture\Constraint\ArrayKey;
use TRegx\PhpUnit\DataProviders\DataProvider;

class DiscussionForumPostingTest extends TestCase
{
    use Seo\DiscussionForumPosting\Fixture,
        Seo\DiscussionForumPosting\SystemDatabase,
        BaseFixture\RelativeUri;

    /**
     * @test
     * @dataProvider fields
     */
    public function canonical(string $field)
    {
        [$schema, $topicId] = $this->schemaTopicInForum('Banana topic', 'apple-forum');
        $this->assertThat(
            $schema[$field],
            $this->relativeUri("/Forum/apple-forum/$topicId-banana_topic"));
    }

    public function fields(): DataProvider
    {
        return DataProvider::list('@id', 'url');
    }

    /**
     * @test
     */
    public function headline()
    {
        $schema = $this->schemaTopicTitle('Banana topic');
        $this->assertSame('Banana topic', $schema['headline']);
    }

    /**
     * @test
     */
    public function contentMarkdown()
    {
        $schema = $this->schemaTopicContent('Lorem *ipsum*.');
        $this->assertSame('Lorem ipsum.', $schema['text']);
    }

    /**
     * @test
     */
    public function contentHtml()
    {
        $schema = $this->schemaTopicContent('Lorem <b>ipsum</b> &copy;.');
        $this->assertSame('Lorem ipsum ©.', $schema['text']);
    }

    /**
     * @test
     */
    public function contentHtmlEntity()
    {
        $schema = $this->schemaTopicContent('&lt;Lorem&gt;');
        $this->assertSame('<Lorem>', $schema['text']);
    }

    /**
     * @test
     */
    public function statistics()
    {
        $schema = $this->schemaForumStatistic(replies:7, likes:5, views:13);
        $this->assertThat(
            $schema['interactionStatistic'],
            $this->logicalAnd(
                $this->interaction('ViewAction', 13),
                $this->interaction('LikeAction', 5),
                $this->interaction('CommentAction', 7),
            ));
    }

    private function interaction(string $type, int $count): Constraint
    {
        return $this->containsEqual([
            '@type'                => 'InteractionCounter',
            'interactionType'      => "https://schema.org/$type",
            'userInteractionCount' => $count,
        ]);
    }

    /**
     * @test
     */
    public function authorUser()
    {
        $schema = $this->postingSchema($this->newTopicAuthorUsername('mark'));
        $this->assertThat(
            $schema['author'],
            $this->logicalAnd(
                new ArrayKey('@type', $this->identicalTo('Person')),
                new ArrayKey('name', $this->identicalTo('mark')),
            ));
    }

    /**
     * @test
     */
    public function authorLegacyGuest()
    {
        $schema = $this->postingSchema($this->newTopicAuthorLegacyGuest('john'));
        $this->assertThat(
            $schema['author'],
            $this->identicalTo(['@type' => 'Person', 'name' => 'john']));
    }

    /**
     * @test
     */
    public function authorUserUrl()
    {
        $user = $this->newUser();
        $schema = $this->postingSchema($this->newTopicAuthorUser($user));
        $this->assertThat(
            $schema['author']['url'],
            $this->relativeUri("/Profile/$user->id"));
    }

    /**
     * @test
     */
    public function datePublished()
    {
        $this->systemDatabaseTimezone('America/Los_Angeles');
        $schema = $this->schemaTopicCreatedAt('2016-01-23 11:53:20', timezone:'Europe/Stockholm');
        $this->assertThat($schema['datePublished'],
            $this->identicalTo('2016-01-23T02:53:20-08:00'));
    }
}
