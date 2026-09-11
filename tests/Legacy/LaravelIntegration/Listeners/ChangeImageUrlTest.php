<?php
namespace Tests\Legacy\LaravelIntegration\Listeners;

use Coyote\Listeners\ChangeImageUrl;
use Illuminate\Mail\Events\MessageSending;
use Tests\Legacy\LaravelIntegration\TestCase;

class ChangeImageUrlTest extends TestCase
{
    public function testFixEmoticonsUrl()
    {
        // given
        $message = new \Symfony\Component\Mime\Email();
        $message->to('fake@fake');
        $message->from('fake@fake');
        $message->html('<img src="//static.4programmers.net/img/smilies/sad.gif">');
        // when
        $listener = new ChangeImageUrl();
        $listener->handle(new MessageSending($message));
        // then
        $this->assertSame('<img src="https://static.4programmers.net/img/smilies/sad.gif">', $message->getHtmlBody());
    }
}
