<?php
namespace Coyote\Notifications\Forum;

use Coyote\Notifications\Post\AbstractNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TopicCreatedNotification extends AbstractNotification implements ShouldQueue
{
    const ID = \Coyote\Notification::FORUM_SUBSCRIBER;

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject($this->getMailSubject())
            ->line(\sPrintF(
                '<strong>%s</strong> utworzył nowy wątek <strong>%s</strong> w kategorii <strong>%s</strong>',
                htmlentities($this->notifier->name),
                htmlentities($this->post->topic->title),
                htmlentities($this->post->forum->name),
            ))
            ->line('<hr>')
            ->line($this->post->html)
            ->line('<hr>')
            ->action('Zobacz wątek', url($this->redirectionUrl()));
    }

    protected function getMailSubject(): string
    {
        return $this->notifier->name . ' utworzył nowy wątek w kategorii ' . $this->post->forum->name . ': ' . $this->post->topic->title;
    }
}
