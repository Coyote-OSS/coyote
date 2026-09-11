<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Job;

use Coyote\Job;
use Coyote\Notifications\Job\ApplicationConfirmationNotification;
use Coyote\Notifications\Job\ApplicationSentNotification;
use Faker\Factory;
use Illuminate\Support\Facades\Notification;
use Tests\Legacy\LaravelIntegration\TestCase;

class ApplicationControllerTest extends TestCase
{
    /**
     * @var Job
     */
    private $job;

    public function setUp(): void
    {
        parent::setUp();

        $this->job = factory(Job::class)->create();
    }

    public function testSubmitInvalidApplication()
    {
        $faker = Factory::create();

        $response = $this->json(
            'POST',
            route('job.application', [$this->job->id]),
            [
                'email' => $faker->email,
                'phone' => $faker->phoneNumber,
                'text' => '"Lorem" \'ipsum\'',
                'salary' => 'od 1000 zł m-c',
                'dismissal_period' => '3 dni robocze'
            ]
        );

        $response->assertJsonValidationErrors(['name']);
    }

    public function testSubmitValidApplication()
    {
        $faker = Factory::create();

        Notification::fake();

        $response = $this->json(
            'POST',
            route('job.application', [$this->job->id]),
            [
                'email' => $fakeEmail = $faker->email,
                'name' => $faker->name,
                'phone' => $faker->phoneNumber,
                'text' => '"Lorem" \'ipsum\'',
                'salary' => 'od 1000 zł m-c',
                'dismissal_period' => '3 dni robocze'
            ]
        );

        $response->assertOk();

        $this->assertTrue($this->job->applications()->where('email', $fakeEmail)->exists());

        Notification::assertSentTo($this->job, ApplicationSentNotification::class);
        Notification::assertSentTo($this->job->applications()->first(), ApplicationConfirmationNotification::class);
    }
}
