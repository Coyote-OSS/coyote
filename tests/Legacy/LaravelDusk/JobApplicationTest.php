<?php

namespace Tests\Legacy\LaravelDusk;

use Coyote\Job;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use Mpdf\Mpdf;

class JobApplicationTest extends DuskTestCase
{
    use WithFaker;

    public function testSuccessfulSubmitJobApplication()
    {
        try {
            $mpdf = new Mpdf(['tempDir' => storage_path('app')]);
            $mpdf->WriteHTML($this->faker->text);

            file_put_contents(storage_path('demo.pdf'), $mpdf->Output('', 'S'));

            $this->browse(function (Browser $browser) {
                $job = factory(Job::class)->create(['email' => $this->faker->email]);

                $browser
                    ->visit('/Praca/Application/' . $job->id)
                    ->resize(1920, 1080)
                    ->check('label[for="enable-invoice"]')
                    ->attach('.thumbnail-mask', storage_path('demo.pdf'))
                    ->type('name', $name = $this->faker->name)
                    ->type('email', $email = $this->faker->email)
                    ->type('phone', $phone = $this->faker->phoneNumber)
                    ->type('github', $url = $this->faker->url)
                    ->press('Wyślij')
                    ->waitForText('Zgłoszenie zostało prawidłowo wysłane.');

                $this->assertDatabaseHas('job_applications', ['name' => $name, 'email' => $email, 'phone' => $phone, 'github' => $url]);

                $job = factory(Job::class)->create(['email' => $this->faker->email]);

                $browser->visit('/Praca/Application/' . $job->id)
                    ->assertInputValue('name', $name)
                    ->assertInputValue('email', $email)
                    ->assertInputValue('phone', $phone)
                    ->assertInputValue('github', $url);
            });
        } finally {
            @unlink(storage_path('demo.pdf'));
        }
    }
}
