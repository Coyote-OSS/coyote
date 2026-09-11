<?php
namespace Tests\Legacy\Integration\OAuth\Laravel;

use Illuminate\Config;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\OAuth;
use Tests\Legacy\Integration\OAuth\Fixture\Constraint\UrlContainsQuery;

class LoginTest extends TestCase
{
    use OAuth\Fixture\Assertion;

    /**
     * @test
     */
    public function google()
    {
        /** @var Config\Repository $config */
        $config = $this->laravel->app['config'];
        $config->set('services.google.client_id', 'mystery-client-id');
        $config->set('services.google.redirect', 'http://redirect-url/');

        $this->assertThat(
            $this->oAuthLogin('google'),
            $this->redirect(new UrlContainsQuery([
                'client_id'     => 'mystery-client-id',
                'redirect_uri'  => 'http://redirect-url/',
                'scope'         => 'openid profile email',
                'response_type' => 'code',
            ])));
    }

    /**
     * @test
     */
    public function facebook()
    {
        /** @var Config\Repository $config */
        $config = $this->laravel->app['config'];
        $config->set('services.facebook.client_id', 'mystery-client-id');
        $config->set('services.facebook.redirect', 'http://redirect-url/');

        $this->assertThat(
            $this->oAuthLogin('facebook'),
            $this->redirect(new UrlContainsQuery([
                'client_id'     => 'mystery-client-id',
                'redirect_uri'  => 'http://redirect-url/',
                'scope'         => 'email',
                'response_type' => 'code',
            ])));
    }

    /**
     * @test
     */
    public function github()
    {
        /** @var Config\Repository $config */
        $config = $this->laravel->app['config'];
        $config->set('services.github.client_id', 'mystery-client-id');
        $config->set('services.github.redirect', 'http://redirect-url/');

        $this->assertThat(
            $this->oAuthLogin('github'),
            $this->redirect(new UrlContainsQuery([
                'client_id'     => 'mystery-client-id',
                'redirect_uri'  => 'http://redirect-url/',
                'scope'         => 'user:email',
                'response_type' => 'code',
            ])));
    }
}
