<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Microblog;

use Coyote\Events\MicroblogSaved;
use Coyote\Microblog;
use Coyote\Notifications\Microblog\DeletedNotification;
use Coyote\Tag;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\Legacy\LaravelIntegration\TestCase;

class SubmitControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function testSubmitWithEmptyText()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->json('POST', '/Mikroblogi/Edit');
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['text']);
    }

    public function testSubmitWithTooManyTags()
    {
        $user = factory(User::class)->create(['reputation' => 3000]);
        $tags = factory(Tag::class, 6)->create();

        $response = $this->actingAs($user)->json(
            'POST',
            '/Mikroblogi/Edit', [
                'text' => $this->faker->text(),
                'tags' => $tags->toArray()
            ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['tags']);
        $response->assertJsonFragment([
            'errors' => [
                'tags' => ['Możesz przypisać maksymalnie 5 tagów.']
            ]
        ]);
    }

    public function testSubmitUnauthenticated()
    {
        $response = $this->json('POST', '/Mikroblogi/Edit');
        $response->assertStatus(401);

        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testSubmitValid()
    {
        $fake = Factory::create();
        $user = factory(User::class)->create();
        $text = $fake->realText();

        $this->actingAs($user)->json('POST', '/Mikroblogi/Edit', ['text' => $text]);
        $this->assertDatabaseHas('microblogs', ['text' => $text]);

        $after = clone $user;
        $after->refresh();

        $this->assertGreaterThan($user->reputation, $after->reputation);
    }

    public function testUpdate()
    {
        $fake = Factory::create();

        $microblog = factory(Microblog::class)->create();
        $microblog->load(['user' => function ($builder) {
            return $builder->select();
        }]);

        $response = $this->actingAs($microblog->user)->json('POST', '/Mikroblogi/Edit/' . $microblog->id, ['text' => $text = $fake->text]);
        $response->assertStatus(200);

        $response->assertJsonFragment(['text' => $text]);
    }

    public function testUpdateAndThrowUnauthorized()
    {
        $fake = Factory::create();

        $microblog = factory(Microblog::class)->create();
        $microblog->load(['user' => function ($builder) {
            return $builder->select();
        }]);

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->json('POST', '/Mikroblogi/Edit/' . $microblog->id, ['text' => $text = $fake->text]);
        $response->assertStatus(403);

        $response->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function testDeleteAndSendNotification()
    {
        $microblog = factory(Microblog::class)->create();
        $user = factory(User::class)->state('admin')->create();

        event(new MicroblogSaved($microblog));

        $this->assertDatabaseHas('pages', ['content_id' => $microblog->id, 'content_type' => Microblog::class]);

        Notification::fake();

        $response = $this->actingAs($user)->json('DELETE', '/Mikroblogi/Delete/' . $microblog->id);
        $response->assertStatus(200);

        $microblog->refresh();

        $this->assertNotNull($microblog->deleted_at);

        Notification::assertSentTo($microblog->user, DeletedNotification::class);
    }
}
