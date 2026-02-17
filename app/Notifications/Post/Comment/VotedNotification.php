<?php

namespace Coyote\Notifications\Post\Comment;

use Coyote\Notifications\Post\AbstractNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VotedNotification extends AbstractNotification implements ShouldQueue {
    const ID = \Coyote\Notification::POST_COMMENT_VOTED;

    public function toMail(): MailMessage {
        $line = $this->notifier->name . ' docenił Twój komentarz w wątku <b>' . \htmlEntities($this->post->topic->title) . '</b>';
        return (new MailMessage)
            ->subject($this->getMailSubject())
            ->line($line)
            ->action('Zobacz post', url($this->redirectionUrl()));
    }

    protected function getMailSubject(): string {
        return $this->notifier->name . ' docenił(a) Twój komentarz';
    }
}
