<?php

namespace Tests\Legacy\LaravelIntegration\Listeners;

use Coyote\Events\SuccessfulLogin;
use Coyote\Stream;
use Coyote\User;
use Faker\Factory;
use Illuminate\Support\Facades\Mail;
use Tests\Legacy\LaravelIntegration\TestCase;

class SendSuccessfulLoginTest extends TestCase
{
    /**
     * @var \Faker\Generator
     */
    private $faker;

    public function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->faker = Factory::create();
    }

    public function testSendSuccessfulLoginEmail()
    {
        $user = factory(User::class)->create(['visits' => 1, 'ip' => $this->faker->ipv4]);

        event(new SuccessfulLogin($user, $ip = $this->faker->ipv4, $browser = $this->faker->userAgent));

        Mail::assertSent(\Coyote\Mail\SuccessfulLogin::class, function ($mail) use ($user, $ip, $browser) {
            return $mail->hasTo($user->email);
        });
    }

    public function testDontSendSuccessfulLoginEmail()
    {
        $user = factory(User::class)->create();

        event(new SuccessfulLogin($user, $this->faker->ipv4, $this->faker->userAgent));

        Mail::assertNotSent(\Coyote\Mail\SuccessfulLogin::class);
    }

    public function testDontSendSuccessfulLoginEmailCauseExistsInStream()
    {
        $user = factory(User::class)->create(['ip' => $this->faker->ipv4]);

        $browser = $this->faker->userAgent;

        Stream::forceCreate(['verb' => 'login', 'ip' => $this->faker->ipv4, 'browser' => $browser, 'actor' => ["id" => $user->id]]);

        event(new SuccessfulLogin($user, $this->faker->ipv4, $browser));

        Mail::assertNotSent(\Coyote\Mail\SuccessfulLogin::class);
    }
}
