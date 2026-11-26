<?php
namespace Tests\Integration\Forum;

use Coyote\Forum;
use Coyote\Notification as NotificationModel;
use Coyote\Notifications\Forum\TopicCreatedNotification;
use Coyote\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Forum\ModelsDriver;
use Tests\Legacy\Integration\BaseFixture\Server;

class CategorySubscriptionTest extends TestCase {
    use Server\Laravel\Transactional;
    use Server\Http;

    private ModelsDriver $models;

    #[Before]
    public function initializeModels(): void {
        $this->models = new ModelsDriver();
    }

    #[Test]
    public function subscribingCategory_returnsSubscribersCount(): void {
        $forum = $this->newForum('Category');
        $this->server->loginById($this->newUser());
        $response = $this->httpToggleSubscription($forum);
        $response->assertOk();
        $this->assertSame('1', $response->getContent());
    }

    #[Test]
    public function subscriberOfCategory_isNotified_aboutNewTopic(): void {
        // given I subscribed a category
        $forum = $this->newForum('Category');
        $subscriberId = $this->newUser();
        $this->server->loginById($subscriberId);
        $this->httpToggleSubscription($forum);
        // when another user creates a topic in that category
        Notification::fake();
        $this->createTopicAs($forum, $this->newUser(), 'Brand new topic');
        // then I am notified on site and by email
        Notification::assertSentTo(
            User::query()->find($subscriberId),
            TopicCreatedNotification::class,
            fn(TopicCreatedNotification $notification, array $channels) => $notification->post->topic->title === 'Brand new topic'
                && \in_array(NotificationModel::MAIL, $channels)
                && \in_array(\Coyote\Services\Notification\DatabaseChannel::class, $channels));
    }

    #[Test]
    public function unsubscribedCategory_doesNotNotify(): void {
        // given I subscribed and then unsubscribed a category
        $forum = $this->newForum('Category');
        $subscriberId = $this->newUser();
        $this->server->loginById($subscriberId);
        $this->httpToggleSubscription($forum);
        $response = $this->httpToggleSubscription($forum);
        $this->assertSame('0', $response->getContent());
        // when another user creates a topic in that category
        Notification::fake();
        $this->createTopicAs($forum, $this->newUser(), 'Brand new topic');
        // then I am not notified
        Notification::assertNotSentTo(User::query()->find($subscriberId), TopicCreatedNotification::class);
    }

    #[Test]
    public function subscriberOfCategory_isNotNotified_aboutOwnTopic(): void {
        $forum = $this->newForum('Category');
        $subscriberId = $this->newUser();
        $this->server->loginById($subscriberId);
        $this->httpToggleSubscription($forum);
        Notification::fake();
        $this->createTopicAs($forum, $subscriberId, 'My very own topic');
        Notification::assertNotSentTo(User::query()->find($subscriberId), TopicCreatedNotification::class);
    }

    #[Test]
    public function subscriberOfCategory_isNotNotified_aboutReplyInTopic(): void {
        // given a topic in a category I subscribed
        $forum = $this->newForum('Category');
        $subscriberId = $this->newUser();
        $this->server->loginById($subscriberId);
        $this->httpToggleSubscription($forum);
        Notification::fake();
        $topicId = $this->createTopicAs($forum, $this->newUser(), 'An existing topic');
        // when someone replies in the topic
        $this->server->loginById($this->newUser());
        $this->httpPost("/Forum/$forum->slug/Submit/$topicId", ['text' => 'A reply with enough content to be accepted'])->assertSuccessful();
        // then I am notified only about the topic, not about the reply
        Notification::assertSentToTimes(User::query()->find($subscriberId), TopicCreatedNotification::class, 1);
    }

    #[Test]
    public function subscriberOfParentCategory_isNotNotified_aboutTopicInSubcategory(): void {
        $parent = $this->newForum('Parent');
        $child = $this->newForum('Child', parentId:$parent->id);
        $subscriberId = $this->newUser();
        $this->server->loginById($subscriberId);
        $this->httpToggleSubscription($parent);
        Notification::fake();
        $this->createTopicAs($child, $this->newUser(), 'A topic in child');
        Notification::assertNotSentTo(User::query()->find($subscriberId), TopicCreatedNotification::class);
    }

    #[Test]
    public function subscriberOfCategory_withMailDisabled_isNotNotifiedByMail(): void {
        // given I subscribed a category, but disabled mail for this notification
        $forum = $this->newForum('Category');
        $subscriberId = $this->newUser();
        $this->server->loginById($subscriberId);
        $this->httpToggleSubscription($forum);
        $this->laravel->databaseTable('notification_settings')
            ->where('user_id', $subscriberId)
            ->where('type_id', NotificationModel::FORUM_SUBSCRIBER)
            ->where('channel', NotificationModel::MAIL)
            ->update(['is_enabled' => false]);
        // when another user creates a topic
        Notification::fake();
        $this->createTopicAs($forum, $this->newUser(), 'Brand new topic');
        // then I am notified, but not by mail
        Notification::assertSentTo(
            User::query()->find($subscriberId),
            TopicCreatedNotification::class,
            fn($notification, array $channels) => !\in_array(NotificationModel::MAIL, $channels));
    }

    #[Test]
    public function notificationSettings_containCategorySubscription(): void {
        $this->server->loginById($this->newUser());
        $this->laravel->get('/User/Notifications/Settings')
            ->assertOk()
            ->assertSee('...nowym wątku w obserwowanej kategorii');
    }

    #[Test]
    public function categoryPage_showsSubscriptionState(): void {
        $forum = $this->newForum('Category');
        $this->server->loginById($this->newUser());
        $this->httpToggleSubscription($forum);
        $this->laravel->get("/Forum/$forum->slug")
            ->assertOk()
            ->assertSee('"is_subscribed":true', escape:false);
    }

    private function httpToggleSubscription(Forum $forum): TestResponse {
        return $this->httpPost("/Forum/$forum->slug/Subscribe", []);
    }

    private function httpPost(string $uri, array $body): TestResponse {
        // submit a valid csrf token, so the request reaches the controller
        // regardless of whether csrf protection is enabled in the environment
        return $this->laravel
            ->withSession(['_token' => 'csrf-token'])
            ->postJson($uri, $body + ['_token' => 'csrf-token']);
    }

    private function createTopicAs(Forum $forum, int $authorId, string $title): int {
        $this->server->loginById($authorId);
        $response = $this->httpPost("/Forum/$forum->slug/Submit", ['title' => $title, 'text' => 'Content of topic']);
        $response->assertCreated();
        return $response->json('topic_id') ?? $this->laravel->databaseTable('topics')->where('title', $title)->value('id');
    }

    private function newUser(): int {
        return $this->models->newUserReturnId(emailConfirmed:true);
    }

    private function newForum(string $name, ?int $parentId = null): Forum {
        // forum slug route pattern accepts letters only
        $slug = $name . \strtr(\uniqid(), '0123456789', 'abcdefghij');
        return Forum::query()->forceCreate([
            'name'        => $slug,
            'slug'        => $slug,
            'title'       => $name,
            'description' => $name,
            'parent_id'   => $parentId,
        ]);
    }
}
