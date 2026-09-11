<?php

namespace Tests\Legacy\LaravelIntegration\Models;

use Carbon\Carbon;
use Coyote\Pm;
use Coyote\Repositories\Contracts\PmRepositoryInterface;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Legacy\LaravelIntegration\TestCase;

class PmTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    private $user;
    private $author;
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = app(PmRepositoryInterface::class);
        $this->user = factory(User::class)->create();
        $this->author = factory(User::class)->create();
    }

    public function testSubmitMessage()
    {
        $this->repo->submit($this->user, ['text' => 'Lorem ipsum lores', 'author_id' => $this->author->id]);

        $this->assertDatabaseHas('pm', ['author_id' => $this->author->id]);
        $after = User::find($this->author->id);

        $this->assertEquals($this->author->pm + 1, $after->pm);
        $this->assertEquals($this->author->pm_unread + 1, $after->pm_unread);
    }

    public function testSubmitMultipleMessagesToUser()
    {
        $this->repo->submit($this->user, ['text' => $this->faker->realText(), 'author_id' => $this->author->id]);
        $this->repo->submit($this->user, ['text' => $this->faker->realText(), 'author_id' => $this->author->id]);

        $this->assertDatabaseHas('pm', ['author_id' => $this->author->id]);

        $after = clone $this->author;
        $after->refresh();

        $this->assertEquals($this->author->pm + 2, $after->pm);
        $this->assertEquals($this->author->pm_unread + 1, $after->pm_unread);
    }

    public function testSubmitMultipleMessagesFromManyUsers()
    {
        $otherUser = factory(User::class)->create();

        $this->repo->submit($this->user, ['text' => $this->faker->realText(), 'author_id' => $this->author->id]);
        $this->repo->submit($otherUser, ['text' => $this->faker->realText(), 'author_id' => $this->author->id]);

        $this->assertDatabaseHas('pm', ['author_id' => $this->author->id]);

        $after = clone $this->author;
        $after->refresh();

        $this->assertEquals($this->author->pm + 2, $after->pm);
        $this->assertEquals($this->author->pm_unread + 2, $after->pm_unread);
    }

    public function testMarkAsRead()
    {
        $pm = $this->repo->submit($this->user, ['text' => 'Lorem ipsum lores', 'author_id' => $this->author->id]);

        $pm[Pm::INBOX]->read_at = Carbon::now();
        $pm[Pm::INBOX]->save();

        $after = clone $this->author;
        $after->refresh();

        $this->assertEquals($this->author->pm + 1, $after->pm);
        $this->assertEquals($this->author->pm_unread, $after->pm_unread);
    }

    public function testPrivateMessageDelete()
    {
        $pm = $this->repo->submit($this->user, ['text' => 'Lorem ipsum lores', 'author_id' => $this->author->id]);
        $pm[Pm::INBOX]->delete();

        $after = clone $this->author;
        $after->refresh();

        $this->assertEquals($this->author->pm, $after->pm);
        $this->assertEquals($this->author->pm_unread, $after->pm_unread);

        // jeden rekord powinien byc w bazie danych...
        $this->assertDatabaseHas('pm', ['user_id' => $this->user->id]);
    }

    public function testCompleteRemovingFromDatabase()
    {
        $pm = $this->repo->submit($this->user, ['text' => 'Lorem ipsum lores', 'author_id' => $this->author->id]);
        $pm[Pm::INBOX]->delete();

        // jeden rekord powinien byc w bazie danych...
        $this->assertDatabaseHas('pm_text', ['id' => $pm[Pm::SENTBOX]->text_id]);

        $pm[Pm::SENTBOX]->delete();
        $this->assertDatabaseMissing('pm_text', ['id' => $pm[Pm::SENTBOX]->text_id]);
    }
}
