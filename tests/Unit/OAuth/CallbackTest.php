<?php
namespace Tests\Unit\OAuth;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\OAuth;
use Tests\Unit\OAuth\Fixture\Constraint\UrlContainsQuery;

class CallbackTest extends TestCase
{
    use BaseFixture\Server\Http;
    use BaseFixture\Server\RelativeUri;
    use OAuth\Fixture\Assertion;
    use OAuth\Fixture\Models;
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
        $this->newUser('John');
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
        $userId = $this->newUserConfirmedEmail('andy@mail');
        $this->oAuthLoggedIn('andy@mail', provider:'github', providerId:'provided-id');
        $this->assertUserProvider($userId, 'github');
        $this->assertUserProviderId($userId, 'provided-id');
    }

    /**
     * @test
     */
    public function failureDuplicateNameDeleted()
    {
        $this->newUserDeleted('Mark');
        $this->assertThat(
            $this->oAuthCallbackUsername('Mark'),
            $this->redirect(new UrlContainsQuery([
                'error' => 'Uuups. Niestety login Mark jest już zajęty.',
            ])));
    }
}
