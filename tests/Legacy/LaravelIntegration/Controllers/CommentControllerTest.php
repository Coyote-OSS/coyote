<?php

namespace Tests\Legacy\LaravelIntegration\Controllers;

use Coyote\Comment;
use Coyote\Guide;
use Coyote\Services\UrlBuilder;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Legacy\LaravelIntegration\TestCase;

class CommentControllerTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    private Guide $guide;
    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->guide = factory(Guide::class)->create();
        $this->user = factory(User::class)->create();
    }

    public function testSuccessfullySubmitComment()
    {
        $text = $this->faker->text;

        $response = $this->actingAs($this->user)->json('POST', '/Comment', ['text' => $text, 'resource_id' => $this->guide->id, 'resource_type' => Guide::class]);
        $response->assertStatus(201);

        $id = $response->json('id');

        $response->assertJsonFragment([
            'text' => $text,
            'url' => UrlBuilder::url($this->guide) . '#comment-' . $id
        ]);
    }

    public function testSuccessfullyReplyComment()
    {
        $text = $this->faker->text;

        $comment = factory(Comment::class)->create(['resource_id' => $this->guide->id, 'resource_type' => Guide::class]);

        $response = $this->actingAs($this->user)->json('POST', '/Comment', ['text' => $text, 'parent_id' => $comment->id]);
        $response->assertStatus(201);

        $response->assertJsonFragment([
            'text' => $text,
            'parent_id' => $comment->id
        ]);
    }

    public function testSuccessfullyUpdateComment()
    {
        $text = $this->faker->text;

        $comment = factory(Comment::class)->create(['resource_id' => $this->guide->id, 'resource_type' => Guide::class]);

        $response = $this->actingAs($comment->user)->json('POST', '/Comment/' . $comment->id, ['text' => $text]);
        $response->assertStatus(200);

        $response->assertJsonFragment([
            'text' => $text
        ]);
    }

    public function testUpdateFailsDueToAuthorizationError()
    {
        $text = $this->faker->text;

        $comment = factory(Comment::class)->create(['resource_id' => $this->guide->id, 'resource_type' => Guide::class]);

        $response = $this->actingAs($this->user)->json('POST', '/Comment/' . $comment->id, ['text' => $text]);
        $response->assertStatus(403);
    }

    public function testSubmitFailsDueToValidationErrors()
    {
        $text = $this->faker->text;

        $response = $this->actingAs($this->user)->json('POST', '/Comment', ['text' => $text]);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['resource_id', 'resource_type']);
        $response->assertJsonFragment([
            'errors' => [
                "resource_id" => ["Pole resource id jest wymagane."],
                "resource_type" => ["Pole resource type jest wymagane."],
            ]
        ]);
    }
}
