<?php
namespace Tests\Legacy\LaravelIntegration\Resources;

use Coyote\Forum;
use Coyote\Http\Resources\PostResource;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Guest;
use Coyote\Topic;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;

class PostResourceTest extends TestCase
{
    use DatabaseTransactions;

    private string $uuid;

    #[Before]
    public function initialize(): void
    {
        $this->uuid = 'b4b94950-11bc-3ab5-bf5b-17d7df81703a';
        PostResource::withoutWrapping();
    }

    #[Test]
    public function testPostUnreadForNewUser()
    {
        /** @var Forum $forum */
        $forum = factory(Forum::class)->create();
        /** @var Topic $topic */
        $topic = factory(Topic::class)->create(['forum_id' => $forum->id]);

        $guest = new Guest($this->uuid);
        $guest->setDefaultSessionTime(now()->subMinutes(5)); // simulate session start

        $tracker = new Tracker($topic, $guest);
        $tracker->setRepository($this->app[TopicRepositoryInterface::class]);

        $resource = (new PostResource($topic->firstPost))->setTracker($tracker)->toResponse(request())->getData(true);

        $this->assertFalse($resource['is_read']);

        $tracker->asRead($topic->last_post_created_at);

        $resource = (new PostResource($topic->firstPost))->setTracker($tracker)->toResponse(request())->getData(true);

        $this->assertTrue($resource['is_read']);
    }
}
