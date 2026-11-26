<?php
namespace Coyote\Listeners;

use Coyote\Events\PostSaved;
use Coyote\Notifications\Forum\TopicCreatedNotification;
use Coyote\Notifications\Post\AbstractNotification;
use Coyote\Notifications\Post\ChangedNotification;
use Coyote\Notifications\Post\SubmittedNotification;
use Coyote\Notifications\Post\UserMentionedNotification;
use Coyote\Post;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Reputation;
use Coyote\Services\Helper;
use Coyote\User;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support;

class DispatchPostNotifications implements ShouldQueue
{
    public function __construct(private Dispatcher $dispatcher, private UserRepository $users) {}

    public function handle(PostSaved $event): void
    {
        $post = $event->post;
        if ($event->wasRecentlyCreated) {
            $this->handlePostCreated($post, $post->user);
        }
        if (!$event->wasRecentlyCreated && $post->editor) {
            $this->handlePostEdited($post, $post->editor, $event->previousPostHtml);
        }
    }

    private function handlePostCreated(Post $post, User $author): void
    {
        $users = $this->postUsersToMention($post, null);
        $this->sendNotificationUserMentioned($post, $author, $users);
        $this->sendNotificationPostCreated($post, $author, $users);
        if ($post->topic->first_post_id === $post->id) {
            $this->sendNotificationTopicCreated($post, $author, $users);
        }
    }

    private function sendNotificationTopicCreated(Post $post, User $author, array $alreadyMentioned): void
    {
        $this->dispatcher->send(
            $this->forumSubscribers($post, $author, $alreadyMentioned),
            new TopicCreatedNotification($author, $post));
    }

    private function forumSubscribers(Post $post, User $notifiableBy, array $exceptSubscribers): Support\Collection
    {
        return $post->forum
            ->subscribers()
            ->whereNotIn('subscriptions.user_id', $exceptSubscribers)
            ->excludeUserAndBlockers($notifiableBy->id)
            ->has('user')
            ->with('user')
            ->get()
            ->pluck('user');
    }

    private function handlePostEdited(Post $post, User $editor, ?string $previousPostHtml): void
    {
        $users = $this->postUsersToMention($post, $previousPostHtml);
        $this->sendNotificationUserMentioned($post, $editor, $users);
        $this->sendNotificationPostEdited($post, $editor, $users);
    }

    private function postUsersToMention(Post $post, ?string $previousHtml): array
    {
        if ($post->user === null) {
            return [];
        }
        if ($post->user->reputation >= Reputation::USER_MENTION) {
            $previouslyMentioned = $this->postUserMentionsOrEmpty($previousHtml);
            $currentlyMentioned = $this->postUserMentionsOrEmpty($post->html);
            return \array_diff($currentlyMentioned, $previouslyMentioned);
        }
        return [];
    }

    private function sendNotificationPostCreated(Post $post, User $author, array $alreadyMentioned): void
    {
        $this->send(
            $this->postCreatedNotifiables($post, $author, $alreadyMentioned),
            new SubmittedNotification($author, $post));
    }

    private function postCreatedNotifiables(Post $post, User $author, array $alreadyMentioned): Support\Collection
    {
        $notifiables = $this
            ->topicSubscribers($post, $author, $alreadyMentioned)
            ->merge($author->followers);
        if ($post->treeParentPost) {
            if ($post->treeParentPost->user->id !== $author->id) {
                $notifiables->add($post->treeParentPost->user);
            }
        }
        return $notifiables;
    }

    private function sendNotificationPostEdited(Post $post, User $editor, array $alreadyMentioned): void
    {
        $this->dispatcher->send(
            $this->postSubscribers($post, $editor, $alreadyMentioned),
            new ChangedNotification($editor, $post));
    }

    private function sendNotificationUserMentioned(Post $post, User $notifier, array $mentionedUserIds): void
    {
        if (empty($mentionedUserIds)) {
            return;
        }
        $this->dispatcher->send(
            $this->users->excludeUserAndBlockers($post->user_id)->findMany($mentionedUserIds),
            new UserMentionedNotification($notifier, $post));
    }

    private function topicSubscribers(Post $post, User $notifiableBy, array $exceptSubscribers): Support\Collection
    {
        return $post->topic
            ->subscribers()
            ->whereNotIn('subscriptions.user_id', $exceptSubscribers)
            ->excludeUserAndBlockers($notifiableBy->id)
            ->has('user')
            ->with('user')
            ->get()
            ->pluck('user');
    }

    private function postSubscribers(Post $post, User $notifiableBy, array $exceptSubscribers): Support\Collection
    {
        return $post
            ->subscribers()
            ->whereNotIn('subscriptions.user_id', $exceptSubscribers)
            ->excludeUserAndBlockers($notifiableBy->id)
            ->has('user')
            ->with('user')
            ->get()
            ->pluck('user');
    }

    private function postUserMentionsOrEmpty(?string $postHtml): array
    {
        if ($postHtml === null) {
            return [];
        }
        return (new Helper\Login)->grab($postHtml);
    }

    private function send(Support\Collection $notifiables, AbstractNotification $notification): void
    {
        $this->dispatcher->send($notifiables->unique('id'), $notification);
    }
}
