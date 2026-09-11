<?php
namespace Tests\Legacy\Integration\OAuth\Fixture;

use Coyote\Domain\OAuth\OAuth;
use Coyote\Domain\OAuth\User;

class ConstantOAuth implements OAuth
{
    public function __construct(private User $user)
    {
    }

    public function user(string $provider): User
    {
        return $this->user;
    }

    public function loginUrl(string $provider): string
    {
        throw new \AssertionError('Failed to login.');
    }
}
