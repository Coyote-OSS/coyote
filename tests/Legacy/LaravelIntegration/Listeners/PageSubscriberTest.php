<?php

namespace Tests\Legacy\LaravelIntegration\Listeners;

use Coyote\Events\MicroblogDeleted;
use Coyote\Events\MicroblogSaved;
use Coyote\Events\TopicSaved;
use Coyote\Listeners\PageSubscriber;
use Coyote\Microblog;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class PageSubscriberTest extends TestCase
{
    use DatabaseTransactions;

    public function testSaveTopicToPagesTable()
    {
        $topic = factory(Topic::class)->create();

        $event = new TopicSaved($topic);

        /** @var PageSubscriber $subscriber */
        $subscriber = $this->app[PageSubscriber::class];
        $subscriber->onTopicSave($event);

        $data = ['content_id' => $topic->id, 'content_type' => Topic::class, 'path' => UrlBuilder::topic($topic)];
        $this->assertDatabaseHas('pages', $data);
    }

    public function testSaveMicroblogsToPagesTable()
    {
        $microblog = factory(Microblog::class)->create();

        $event = new MicroblogSaved($microblog);

        /** @var PageSubscriber $subscriber */
        $subscriber = $this->app[PageSubscriber::class];
        $subscriber->onMicroblogSave($event);

        $data = ['content_id' => $microblog->id, 'content_type' => Microblog::class, 'path' => UrlBuilder::microblog($microblog)];
        $this->assertDatabaseHas('pages', $data);

        $event = new MicroblogDeleted($microblog);
        $subscriber->onMicroblogDelete($event);

        $this->assertDatabaseMissing('pages', $data);
    }
}
