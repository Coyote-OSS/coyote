<?php
namespace Tests\Legacy\LaravelIntegration\Controllers\Api;

use Coyote\Coupon;
use Coyote\Firm;
use Coyote\Http\Controllers\Api\JobsController;
use Coyote\Http\Resources\Api\JobApiResource;
use Coyote\Job;
use Coyote\Payment;
use Coyote\Services\Parser\Factories\JobFactory;
use Coyote\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Legacy\LaravelIntegration\TestCase;

class JobsControllerTest extends TestCase
{
    use WithFaker;

    private User $user;
    private $token;

    public function setUp(): void
    {
        parent::setUp();

        /**
         * Internal calls of JobsController use {@see JobApiResource::$parser},
         * which is a static field, for some reason. Granted that's poor design,
         * but in the application it works fairly okay, though is subject to refactor.
         * However when running tests, that static field adds an implicit tight
         * coupling between the tests - the tests can influence each other.
         * Short debugging shows that {@see JobFactory} instance is being reused
         * between tests, because <code>'parser.job'</code> instance is being
         * kept in the static fields. Ugh.
         * To force the {@see JobsController} to regenerate new instance of
         * {@see JobFactory}, we set the static field to null, since the code
         * is instructed to create a new instance if the field is null.
         * My best bet is that laravel after a request cycle destroys or
         * finalizes the containers, but the kept instance of {@see JobFactory}
         * still has a reference to the old (now destroyed) containers.
         * When a new refresh cycle begins, laravel creates new containers,
         * but doesn't populate the new instance to all instances of {@see JobFactory}
         * since it's kept in a static field.
         */

        JobApiResource::$parser = null;

        $this->user = factory(User::class)->create();
        $this->token = $this->user->createToken('4programmers.net')->accessToken;
    }

    public function testGetSingleJob()
    {
        $job = factory(Job::class)->create(['user_id' => $this->user->id]);

        $response = $this->get('/v1/jobs/' . $job->id, ['Accept' => 'application/json', 'Content-type' => 'application/json']);

        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJsonFragment([
            'title'       => $job->title,
            'salary_from' => $job->salary_from,
            'salary_to'   => $job->salary_to,
        ]);
    }

    public function testSubmitWithInvalidPlanName()
    {
        $data = [
            'title' => $this->faker->text(60),
            'plan'  => 'xxxx',
        ];

        $response = $this->json('POST', '/v1/jobs', $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['plan']);
        $response->assertJson([
            'message' => 'Invalid plan name.',
            'errors'  => [
                'plan' => ['Invalid plan name.'],
            ],
        ]);
    }

    public function testSuccessfulSubmitWithStandardPlan()
    {
        $coupon = Coupon::query()->create(['amount' => 159, 'code' => str_random(), 'user_id' => $this->user->id]);

        $data = [
            'title'       => $this->faker->text(60),
            'salary_from' => 3000,
            'salary_to'   => 5000,
            'rate'        => 'weekly',
            'currency'    => 'USD',
            'plan'        => 'premium',
            'seniority'   => 'lead',
            'employment'  => 'mandatory',
            'recruitment' => $this->faker->url,
            'is_gross'    => true,
            'locations'   => [
                [
                    'city'          => 'Wrocław',
                    'country'       => 'Polska',
                    'street'        => 'Rynek',
                    'street_number' => '23',
                ],
            ],
        ];

        $response = $this->json('POST', '/v1/jobs', $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);

        $this->assertEquals(201, $response->getStatusCode());
        $response->assertJsonFragment([
            'title'       => $data['title'],
            'currency'    => $data['currency'],
            'salary_from' => $data['salary_from'],
            'salary_to'   => $data['salary_to'],
            'rate'        => $data['rate'],
            'employment'  => $data['employment'],
            'seniority'   => $data['seniority'],
            'is_gross'    => true,
            'is_remote'   => false,
        ]);

        $this->assertNotNull(Coupon::withTrashed()->find($coupon->id)->deleted_at, 'Failed asserting that coupon is deleted.');

        /** @var Job $job */
        $job = Job::find($response->json('id'));

        $this->assertFalse($job->enable_apply);
        $this->assertEquals($job->seniority, $data['seniority']);
        $this->assertEquals($job->employment, $data['employment']);
        $this->assertEquals($job->rate, $data['rate']);
        $this->assertEquals($job->locations[0]->city, $data['locations'][0]['city']);
        $this->assertEquals($job->locations[0]->street, $data['locations'][0]['street']);
        $this->assertEquals($job->locations[0]->street_number, $data['locations'][0]['street_number']);
        $this->assertEquals($job->locations[0]->country->name, $data['locations'][0]['country']);
        $this->assertTrue($job->is_publish);

        $payment = $job->payments->first();
        $this->assertEquals(Payment::PAID, $payment->status_id);
    }

    public function testSubmitWithFirm()
    {
        Coupon::query()->create(['amount' => 159, 'code' => str_random(), 'user_id' => $this->user->id]);

        $data = [
            'title' => $this->faker->text(60),
            'plan'  => 'premium',
            'firm'  => [
                'name' => $this->faker->company,
                'logo' => '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',
            ],
        ];

        $response = $this->json('POST', '/v1/jobs', $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);
        $this->assertEquals(201, $response->getStatusCode());

        $response->assertJsonFragment([
            'title'       => $data['title'],
            'currency'    => 'PLN',
            'salary_from' => null,
            'salary_to'   => null,
            'rate'        => 'monthly',
            'employment'  => 'employment',
            'seniority'   => null,
            'is_gross'    => false,
            'is_remote'   => false,
        ]);

        $logo = $response->json('firm.logo');

        $this->assertNotEmpty($logo);
    }

    public function testSubmitWithAlreadyCreatedFirm()
    {
        Coupon::query()->create(['amount' => 159, 'code' => str_random(), 'user_id' => $this->user->id]);
        $firm = factory(Firm::class)->create(['user_id' => $this->user->id]);
        $data = [
            'title' => $this->faker->text(60),
            'plan'  => 'premium',
            'tags'  => [
                ['name' => 'php'],
            ],
            'firm'  => [
                'name' => $firm->name,
            ],
        ];
        $response = $this->json('POST', '/v1/jobs', $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);
        $this->assertEquals(201, $response->getStatusCode());
        $result = json_decode($response->getContent(), true);
        $this->assertEquals($result['firm']['name'], $firm->name);
        $this->assertEquals($result['firm']['website'], $firm->website);
        $this->assertEquals($result['tags'][0]['name'], 'php');
        $data = ['title' => $data['title']];
        $response = $this->json('PUT', '/v1/jobs/' . $result['id'], $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertJsonFragment(['title' => $data['title']]);
    }

    public function testNotEnoughFunds()
    {
        $response = $this->json('POST', '/v1/jobs',
            ['title' => $this->faker->title, 'plan' => 'premium'],
            ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);
        $this->assertEquals(422, $response->getStatusCode());
        $response->assertJsonFragment(['errors' => ['plan' => ['No sufficient funds to post this job offer.']]]);
    }

    public function testSuccessfulSubmitWithFreePlan()
    {
        $data = [
            'title'       => $this->faker->text(60),
            'salary_from' => 3000,
            'salary_to'   => 5000,
            'rate'        => 'weekly',
            'currency'    => 'USD',
            'plan'        => 'free',
            'seniority'   => 'lead',
            'employment'  => 'mandatory',
            'recruitment' => $this->faker->url,
            'is_gross'    => true,
            'locations'   => [
                [
                    'city'          => 'Wrocław',
                    'country'       => 'Polska',
                    'street'        => 'Rynek',
                    'street_number' => '23',
                ],
            ],
        ];

        $response = $this->json('POST', '/v1/jobs', $data, ['Authorization' => 'Bearer ' . $this->token, 'Accept' => 'application/json']);

        $this->assertEquals(201, $response->getStatusCode());
        $response->assertJsonFragment([
            'title'       => $data['title'],
            'currency'    => $data['currency'],
            'salary_from' => $data['salary_from'],
            'salary_to'   => $data['salary_to'],
            'rate'        => $data['rate'],
            'employment'  => $data['employment'],
            'seniority'   => $data['seniority'],
            'is_gross'    => true,
            'is_remote'   => false,
        ]);

        /** @var Job $job */
        $job = Job::find($response->json('id'));

        $payment = $job->payments->first();
        $this->assertEquals(Payment::PAID, $payment->status_id);
    }
}
