<?php

namespace Tests\Legacy\LaravelIntegration\Policies;

use Coyote\Forum;
use Coyote\Group;
use Coyote\User;
use Faker\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Tests\Legacy\LaravelIntegration\TestCase;

class ForumPolicyTest extends TestCase
{
    /**
     * @var Forum
     */
    protected $forum;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->forum = factory(Forum::class)->make();
        $this->user = factory(User::class)->make(['id' => $this->faker->numberBetween()]);
    }

    public function testAccessToDefaultForumIsAllowed()
    {
        $this->assertTrue(Gate::allows('access', $this->forum));
        $this->assertTrue($this->user->can('access', $this->forum));
    }

    public function testAccessToRestrictedForumIsNotAllowed()
    {
        $group = factory(Group::class)->make();

        $this->forum->is_prohibited = true;
        $this->forum->groups = collect([$group]);

        $this->assertFalse(Gate::allows('access', $this->forum));
        $this->assertFalse($this->user->can('access', $this->forum));
    }

    public function testAccessToRestrictedForumIsAllowed()
    {
        $group = factory(Group::class)->make(['id' => $this->faker->numberBetween()]);

        $this->forum->is_prohibited = true;
        $this->forum->groups = $this->user->groups = collect([$group]);

        $this->assertTrue($this->user->can('access', $this->forum));
    }

    public function testWriteInLockedForumIsNotAllowed()
    {
        $this->forum->is_locked = true;

        $this->assertFalse($this->user->can('update', $this->forum));
        $this->assertFalse($this->user->can('write', $this->forum));
    }

    public function testWriteInAnonymousCategoryIsAllowed()
    {
        $this->forum->enable_anonymous = true;

        $this->assertTrue(Gate::allows('write', $this->forum));
    }

    public function testWriteInNonAnonymousCategoryIsNotAllowed()
    {
        $this->assertFalse(Gate::allows('write', $this->forum));
        $this->assertTrue($this->user->can('write', $this->forum));
    }

    public function testWriteInLockedCategoryAsAdminIsAllowed()
    {
        $this->forum->is_locked = true;

        Gate::define('forum-update', function () {
            return true;
        });

        $this->assertTrue($this->user->can('update', $this->forum));
        $this->assertTrue($this->user->can('write', $this->forum));
    }

    public function testDeleteForumIsAllowed()
    {
        Auth::setUser($this->user);

        Gate::define('forum-delete', function () {
            return true;
        });

        $this->assertTrue($this->user->can('delete', $this->forum));
        $this->assertTrue(Gate::allows('forum-delete'));
        $this->assertTrue(Gate::allows('delete', $this->forum));
        $this->assertTrue(Gate::forUser($this->user)->allows('delete', $this->forum));
    }
}
