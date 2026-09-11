<?php
namespace Tests\Legacy\Integration\OAuth;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\OAuth;
use Tests\Legacy\Integration\OAuth\Fixture\Constraint\UrlBasepath;
use Tests\Legacy\Integration\OAuth\Fixture\Constraint\UrlContainsQuery;

class LoginTest extends TestCase
{
    use OAuth\Fixture\Assertion;

    /**
     * @test
     */
    public function redirectStatusCode()
    {
        $this->assertSame(
            302,
            $this->oAuthLogin('facebook')->status());
    }

    /**
     * @test
     */
    public function redirectUrlGoogle()
    {
        $this->assertThat(
            $this->oAuthLogin('google'),
            $this->redirect(new UrlBasepath('https://accounts.google.com/o/oauth2/auth')));
    }

    /**
     * @test
     */
    public function redirectUrlFacebook()
    {
        $this->assertThat(
            $this->oAuthLogin('facebook'),
            $this->redirect(new UrlBasepath('https://www.facebook.com/v3.3/dialog/oauth')));
    }

    /**
     * @test
     */
    public function redirectUrlGithub()
    {
        $this->assertThat(
            $this->oAuthLogin('github'),
            $this->redirect(new UrlBasepath('https://github.com/login/oauth/authorize')));
    }

    /**
     * @test
     */
    public function responseType()
    {
        $this->assertThat(
            $this->oAuthLogin('google'),
            $this->redirect(new UrlContainsQuery(['response_type' => 'code'])));
    }
}
