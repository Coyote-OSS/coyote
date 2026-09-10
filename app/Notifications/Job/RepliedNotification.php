<?php

namespace Coyote\Notifications\Job;

use Coyote\Comment;
use Coyote\Services\UrlBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RepliedNotification extends Notification
{
    use Queueable;

    public function __construct(private Comment $comment)
    {
    }

    /**
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * @return MailMessage
     */
    public function toMail()
    {
        $url = UrlBuilder::url($this->comment->resource);

        return (new MailMessage())
            ->subject('Odpowiedź na Twój komentarz na stronie  ' . $this->comment->resource->title)
            ->line(
                sprintf(
                    'Udzielono odpowiedzi na Twój komentarz na stronie <strong>%s</strong>.',
                    link_to($url, htmlentities($this->comment->resource->title))
                )
            )
            ->action('Kliknij, aby ją zobaczyć', "$url#comment-{$this->comment->id}");
    }
}
