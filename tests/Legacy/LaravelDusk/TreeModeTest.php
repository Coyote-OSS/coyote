<?php
namespace Tests\Legacy\LaravelDusk;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;

class TreeModeTest extends DuskTestCase
{
    private Driver $driver;

    #[Before]
    public function initialize(): void
    {
        $this->driver = new Driver();
    }

    #[Test]
    public function userWithAlphaAccess_canSee_discussModeField(): void
    {
        $this->browse(function (Browser $browser) {
            $this->driver->loginAsUser($browser, alpha:true);
            $this->driver->visit($browser, $this->driver->createTopicUrl());
            $browser->assertSee('Rodzaj wątku');
        });
    }

    #[Test]
    public function userRegular_canNotSee_discussModeField(): void
    {
        $this->browse(function (Browser $browser) {
            $this->driver->loginAsUserRegular($browser);
            $this->driver->visit($browser, $this->driver->createTopicUrl());
            $browser->assertDontSee('Rodzaj wątku');
        });
    }

    #[Test]
    public function topicDiscussMode_canBeCreated_usingSubmitForm(): void
    {
        $this->browse(function (Browser $browser) {
            $this->driver->loginAsUser($browser, alpha:true);
            $this->driver->visit($browser, $this->driver->createTopicUrl());
            $browser->waitForText('Bądź rzeczowy.');
            $browser->type('title', 'New topic title');
            $this->driver->typeMarkdown($browser, 'First post');
            $browser->select('discussMode', 'tree');
            $browser->press('Dodaj post');
            $browser->waitUntilMissingText('Bądź rzeczowy.');
            $browser->assertPathIs('/Forum/Newbie/*-*');
            $browser->assertDontSee('Komentuj');
        });
    }

    #[Test]
    public function topicModeLinear_hasComments(): void
    {
        $this->browse(function (Browser $browser) {
            $topic = $this->driver->seedTopic(mode:'linear');
            $this->driver->visit($browser, $this->driver->topicUrl($topic));
            $browser->assertSee('Komentuj');
        });
    }

    #[Test]
    public function treeModeTopic_doesNotHaveComments(): void
    {
        $this->browse(function (Browser $browser) {
            $topic = $this->driver->seedTopic(mode:'tree');
            $this->driver->visit($browser, $this->driver->topicUrl($topic));
            $browser->assertDontSee('Komentuj');
        });
    }
}
