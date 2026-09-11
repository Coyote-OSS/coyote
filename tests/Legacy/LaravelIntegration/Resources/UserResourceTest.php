<?php

namespace Tests\Legacy\LaravelIntegration\Resources;

use Coyote\Http\Resources\UserResource;
use Coyote\User;
use Tests\Legacy\LaravelIntegration\TestCase;

class UserResourceTest extends TestCase
{
    public function testSimpleUserModelWithoutOptionalFields()
    {
        $user = factory(User::class)->make(['sig' => null]);

        $this->assertArrayNotHasKey('allow_sig', $user);
        $this->assertArrayNotHasKey('allow_count', $user);
        $this->assertArrayNotHasKey('sig', $user);

        $resource = (new UserResource($user))->resolve(request());

        $this->assertArrayNotHasKey('allow_sig', $resource);
        $this->assertArrayNotHasKey('allow_count', $resource);
        $this->assertArrayNotHasKey('sig', $resource);
    }

    public function testUserModelWithOptionalFields()
    {
        $user = factory(User::class)->make(['sig' => 'foo', 'allow_sig' => true, 'is_online' => false, 'is_blocked' => false]);

        $this->assertArrayHasKey('allow_sig', $user);
        $this->assertArrayHasKey('sig', $user);
        $this->assertArrayHasKey('is_online', $user);
        $this->assertArrayHasKey('is_blocked', $user);

        $resource = (new UserResource($user))->resolve(request());

        $this->assertArrayHasKey('allow_sig', $resource);
        $this->assertArrayHasKey('sig', $resource);
        $this->assertArrayHasKey('is_online', $resource);
        $this->assertArrayHasKey('is_blocked', $resource);

        $this->assertEquals($user->sig, trim($resource['sig']));
        $this->assertFalse($resource['is_online']);
        $this->assertFalse($resource['is_blocked']);
    }
}
