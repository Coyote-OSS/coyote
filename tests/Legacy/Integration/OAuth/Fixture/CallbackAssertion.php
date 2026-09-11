<?php
namespace Tests\Legacy\Integration\OAuth\Fixture;

use Coyote\User;
use PHPUnit\Framework\Assert;

trait CallbackAssertion
{
    function assertUserExists(string $email): void
    {
        Assert::assertNotNull($this->userByEmail($email));
    }

    function assertUserHasName(string $email, string $username): void
    {
        Assert::assertSame($username, $this->userByEmail($email)->name);
    }

    function userByEmail(string $email): ?User
    {
        /** @var User $user */
        $user = User::query()->where(['email' => $email])->first();
        return $user;
    }
}
