<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Job;

use Coyote\Currency;
use Coyote\Firm;
use Coyote\Job;
use Coyote\Models\Asset;
use Coyote\Plan;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Legacy\LaravelIntegration\TestCase;

class SubmitControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    public function testSubmitFailsNoTitleWasProvided()
    {
        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['currency_id', 'plan_id', 'title']);
        $response->assertJsonFragment([
            'title'       => ['Tytuł jest wymagany.'],
            'currency_id' => ['Pole currency id jest wymagane.'],
            'plan_id'     => ['Pole plan id jest wymagane.'],
        ]);
    }

    public function testSubmitFailsNoEmailWasProvided()
    {
        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit', [
            'title'        => $this->faker->text(60),
            'plan_id'      => Plan::active()->first()->id,
            'currency_id'  => Currency::first()->id,
            'email'        => '',
            'enable_apply' => true,
            'apply_type'   => 'service',
        ]);

        $response->assertJsonValidationErrors(['email']);
        $response->assertJsonFragment([
            'email' => ['Pole email jest wymagane.'],
        ]);
    }

    public function testSubmitFailsNoRecruitmentWasProvided()
    {
        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit', [
            'title'        => $this->faker->text(60),
            'plan_id'      => Plan::query()->active()->first()->id,
            'currency_id'  => Currency::query()->first()->id,
            'email'        => '',
            'enable_apply' => false,
            'apply_type'   => 'description',
        ]);

        $response->assertJsonValidationErrors(['recruitment']);
        $response->assertJsonFragment([
            'recruitment' => ['Proszę podać informacje w jaki sposób można składać aplikacje na to stanowisko.'],
        ]);
    }

    public function testSubmitFailsNoFirmNameWasProvided()
    {
        $firm = factory(Firm::class)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit', [
            'title'        => $this->faker->text(60),
            'plan_id'      => Plan::active()->first()->id,
            'currency_id'  => Currency::first()->id,
            'email'        => $this->faker->email,
            'enable_apply' => true,
            'firm'         => [
                'id'   => $firm->id,
                'name' => '',
            ],
        ]);

        $response->assertJsonValidationErrors(['firm.name']);
        $response->assertJsonFragment([
            'firm.name' => ['Pole nazwa firmy jest wymagane.'],
        ]);
    }

    public function testSubmitValidFormWithoutFirm()
    {
        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit', [
            'title'        => $title = $this->faker->text(60),
            'plan_id'      => Plan::active()->first()->id,
            'currency_id'  => $currency = Currency::first()->id,
            'enable_apply' => true,
            'apply_type'   => 'service',
            'email'        => $this->user->email,
            'description'  => $description = $this->faker->realText(),
            'rate'         => 'weekly',
            'employment'   => 'b2b',
            'seniority'    => 'lead',
            'is_gross'     => true,
            'salary_from'  => 10000,
            'salary_to'    => 20000,
            'locations'    => [
                [
                    'city'      => 'Wrocław',
                    'street'    => 'Rynek',
                    'latitude'  => 51,
                    'longitude' => 17,
                ],
            ],
            'tags'         => [
                [
                    'name'     => 'c#',
                    'priority' => 2,
                ],
            ],
        ]);

        $response->assertStatus(200);

        /** @var Job $job */
        $job = Job::where('user_id', $this->user->id)->first();

        $this->assertEquals($title, $job->title);
        $this->assertEquals($description, $job->description);
        $this->assertEquals($currency, $job->currency_id);
        $this->assertEquals('weekly', $job->rate);
        $this->assertEquals('b2b', $job->employment);
        $this->assertEquals('lead', $job->seniority);
        $this->assertEquals(10000, $job->salary_from);
        $this->assertEquals(20000, $job->salary_to);
        $this->assertTrue($job->is_gross);
        $this->assertTrue($job->deadline_at->isFuture());
        $this->assertEquals('Wrocław', $job->locations[0]->city);
        $this->assertEquals('Rynek', $job->locations[0]->street);
        $this->assertEquals(51, $job->locations[0]->latitude);
        $this->assertEquals(17, $job->locations[0]->longitude);
        $this->assertEquals('c#', $job->tags[0]->name);
        $this->assertEquals(2, $job->tags[0]->pivot->priority);
        $this->assertNull($job->firm_id);
    }

    public function testSubmitValidFormWithFirm()
    {
        /** @var Firm $firm */
        $firm = factory(Firm::class)->make();
        $asset = factory(Asset::class)->create(['name' => 'a.png']);

        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit', [
            'title'        => $title = $this->faker->text(60),
            'plan_id'      => Plan::active()->first()->id,
            'currency_id'  => $currency = Currency::first()->id,
            'enable_apply' => true,
            'apply_type'   => 'service',
            'email'        => $this->user->email,
            'firm'         => $firm->toArray() + [
                    'logo'   => $logo = url('/uploads/logo/b.png'),
                    'assets' => [
                        $asset,
                    ],
                ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('firms', ['user_id' => $this->user->id]);

        /** @var Job $job */
        $job = Job::where('user_id', $this->user->id)->first();

        $this->assertNotEmpty($job->firm_id);
        $this->assertEquals($firm->name, $job->firm->name);
        $this->assertEquals($this->user->id, $job->firm->user_id);
        $this->assertEquals($firm->latitude, $job->firm->latitude);
        $this->assertEquals($firm->longitude, $job->firm->longitude);
        $this->assertEquals($firm->street, $job->firm->street);
        $this->assertEquals($firm->city, $job->firm->city);
        $this->assertEquals($firm->country_id, $job->firm->country_id);
        $this->assertEquals('logo/b.png', $job->firm->logo->getFilename());
        $this->assertEquals('a.png', $job->firm->assets[0]->name);
    }

    public function testSubmitValidFormWithExistingFirm()
    {
        /** @var Firm $firm */
        $firm = factory(Firm::class)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit', [
            'title'        => $this->faker->text(60),
            'plan_id'      => Plan::active()->first()->id,
            'currency_id'  => Currency::first()->id,
            'enable_apply' => true,
            'apply_type'   => 'service',
            'email'        => $this->user->email,
            'firm'         => $firm->toArray(),
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('jobs', ['firm_id' => $firm->id]);
    }

    public function testSubmitShouldFailDueToUnauthorizedException()
    {
        /** @var Job $job */
        $job = factory(Job::class)->create(['user_id' => $this->user->id]);

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->json('POST', '/Praca/Submit/' . $job->id);

        $response->assertStatus(403);
    }

    public function testUpdateTitle()
    {
        $firm = factory(Firm::class)->create(['user_id' => $this->user->id]);
        /** @var Job $job */
        $job = factory(Job::class)->create(['user_id' => $this->user->id, 'firm_id' => $firm->id]);

        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit/' . $job->id, [
            'title'        => $title = $this->faker->text(60),
            'plan_id'      => $plan = Plan::active()->first()->id,
            'currency_id'  => $currency = Currency::first()->id,
            'enable_apply' => true,
            'apply_type'   => 'service',
            'email'        => $email = $this->user->email,
            'firm'         => $firm->toArray(),
        ]);

        $response->assertStatus(200);

        $job->refresh();

        $this->assertEquals($title, $job->title);
        $this->assertEquals($plan, $job->plan_id);
        $this->assertEquals($currency, $job->currency_id);
        $this->assertEquals($email, $job->email);
        $this->assertEquals($firm->id, $job->firm_id);
    }

    public function testUpdateWithDifferentFirm()
    {
        $firm = factory(Firm::class)->create(['user_id' => $this->user->id]);
        /** @var Job $job */
        $job = factory(Job::class)->create(['user_id' => $this->user->id, 'firm_id' => $firm->id]);

        $newFirm = factory(Firm::class)->make();

        $response = $this->actingAs($this->user)->json('POST', '/Praca/Submit/' . $job->id, [
            'title'        => $title = $this->faker->text(60),
            'plan_id'      => Plan::active()->first()->id,
            'currency_id'  => Currency::first()->id,
            'enable_apply' => true,
            'apply_type'   => 'service',
            'email'        => $this->user->email,
            'firm'         => $newFirm->toArray(),
        ]);

        $response->assertStatus(200);

        $job->refresh();

        $this->assertEquals($title, $job->title);
        $this->assertEquals($newFirm->name, $job->firm->name);
        $this->assertNotEquals($firm->id, $job->firm_id);
    }
}
