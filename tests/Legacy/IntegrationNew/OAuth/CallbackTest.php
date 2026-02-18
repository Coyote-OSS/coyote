<?php
namespace Tests\Legacy\IntegrationNew\OAuth;

use Coyote\User;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;
use Tests\Legacy\IntegrationNew\OAuth;
use Tests\Legacy\IntegrationNew\OAuth\Fixture\Constraint\UrlContainsQuery;

class CallbackTest extends TestCase
{
    use BaseFixture\Server\Http;
    use BaseFixture\Server\RelativeUri;
    use BaseFixture\Forum\Models;
    use OAuth\Fixture\Assertion;
    use OAuth\Fixture\CallbackRequests;
    use OAuth\Fixture\CallbackAssertion;

    /**
     * @test
     */
    public function successRedirectHomepage()
    {
        $this->assertThat(
            $this->oAuthCallbackLogged(),
            $this->redirect($this->relativeUri('')));
    }

    /**
     * @test
     */
    public function failureMissingCode()
    {
        $this->assertThat(
            $this->oAuthCallback([]),
            $this->redirect($this->relativeUri('/Login')));
    }

    /**
     * @test
     */
    public function failureRedirectLogin()
    {
        $this->assertThat(
            $this->oAuthCallback(['code' => 'code', 'error' => 'error']),
            $this->redirect($this->relativeUri('/Login')));
    }

    /**
     * @test
     */
    public function failureErrorDescription()
    {
        $this->assertThat(
            $this->oAuthCallback(['error' => 'error', 'error_description' => 'failure message']),
            $this->redirect($this->relativeUri('/Login?error=failure%20message')));
    }

    /**
     * @test
     */
    public function successCreateUser()
    {
        $this->oAuthLoggedIn('mark@mail', username:'Mark');
        $this->assertUserExists('mark@mail');
        $this->assertUserHasName('mark@mail', 'Mark');
    }

    /**
     * @test
     */
    public function failureDuplicateName()
    {
        $this->driver->newUser('John');
        $this->assertThat(
            $this->oAuthCallbackUsername('John'),
            $this->redirect(new UrlContainsQuery([
                'error' => 'Uuups. Niestety login John jest już zajęty.',
            ])));
    }

    /**
     * @test
     */
    public function loginMergeUpdateAccount()
    {
        $this->driver->newUserConfirmedEmail('andy@mail');
        $this->oAuthLoggedIn('andy@mail', provider:'github', providerId:'provided-id');
        /** @var User $user */
        $user = User::query()->where('email', 'andy@mail')->first();
        $this->assertSame('github', $user->provider);
        $this->assertSame('provided-id', $user->provider_id);
    }

    /**
     * @test
     */
    public function failureDuplicateNameDeleted()
    {
        $this->driver->newUserDeleted('Mark');
        $this->assertThat(
            $this->oAuthCallbackUsername('Mark'),
            $this->redirect(new UrlContainsQuery([
                'error' => 'Uuups. Niestety login Mark jest już zajęty.',
            ])));
    }
}
