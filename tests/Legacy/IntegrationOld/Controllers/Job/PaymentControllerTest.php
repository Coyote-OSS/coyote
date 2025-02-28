<?php

namespace Tests\Legacy\IntegrationOld\Controllers\Job;

use Coyote\Country;
use Coyote\Coupon;
use Coyote\Firm;
use Coyote\Job;
use Coyote\Plan;
use Coyote\Services\UrlBuilder;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Legacy\IntegrationOld\TestCase;

class PaymentControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    private Job $job;
    private Country $poland;
    private Country $austria;

    public function setUp(): void
    {
        parent::setUp();

        $this->job = factory(Job::class)->create(['is_publish' => false]);

        $this->assertNotEmpty(config('services.stripe.key'));
        $this->assertNotEmpty(config('services.stripe.secret'));
        $this->assertEquals('testing', config('app.env'));

        $this->poland = Country::query()->where('code', '=', 'PL')->firstOrFail();
        $this->austria = Country::query()->where('code', '=', 'AT')->firstOrFail();
    }

    public function testSubmitInvalidFormWithoutAnyData()
    {
        $response = $this->actingAs($this->job->user)->json('POST', '/Praca/Payment/' . $this->job->getUnpaidPayment()->id);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(
            ['payment_method'],
        );
    }

    public function testSubmitInvalidForm()
    {
        $payment = $this->job->getUnpaidPayment();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            ['payment_method' => 'card', 'price' => $payment->plan->gross_price],
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(
            ['invoice.address', 'invoice.name', 'invoice.city', 'invoice.postal_code'],
        );

        $response->assertJson([
            'message' => 'Pole nazwa jest wymagane. (and 4 more errors)',
            'errors'  => [
                'invoice.name'        => ['Pole nazwa jest wymagane.'],
                'invoice.address'     => ['Pole adres jest wymagane.'],
                'invoice.city'        => ['Pole miasto jest wymagane.'],
                'invoice.postal_code' => ['Pole kod pocztowy jest wymagane.'],
            ],
        ]);
    }

    public function testSubmitFormWithoutCountryId()
    {
        $payment = $this->job->getUnpaidPayment();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price'          => $payment->plan->gross_price,
                'invoice'        => [
                    'name'        => $this->faker->company,
                    'vat_id'      => '123123123',
                    'address'     => $this->faker->address,
                    'city'        => $this->faker->city,
                    'postal_code' => $this->faker->postcode,
                ],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(
            ['invoice.country_id'],
        );

        $response->assertJson([
            'message' => 'Pole kraj jest wymagane.',
            'errors'  => [
                'invoice.country_id' => ['Pole kraj jest wymagane.'],
            ],
        ]);
    }

    public function testSubmitValidFormWithInvoice()
    {
        $faker = Factory::create();
        $payment = $this->job->getUnpaidPayment();

        $this->assertTrue($payment->plan->gross_price > 0);

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price'          => $payment->plan->gross_price,
                'invoice'        => [
                    'name'        => $name = $faker->company,
                    'vat_id'      => $vat = '8943139460',
                    'country_id'  => $this->poland->id,
                    'address'     => $address = $faker->address,
                    'city'        => $city = $faker->city,
                    'postal_code' => $postalCode = $faker->postcode,
                ],
            ]);

        $response->assertStatus(200);

        $data = $response->decodeResponseJson();

        $this->assertNotEmpty($data['token']);

        $payment->refresh();

        $this->assertEquals($payment->invoice->name, $name);
        $this->assertEquals($payment->invoice->vat_id, $vat);
        $this->assertEquals($payment->invoice->country_id, $this->poland->id);
        $this->assertEquals($payment->invoice->address, $address);
        $this->assertEquals($payment->invoice->city, $city);
        $this->assertEquals($payment->invoice->postal_code, $postalCode);
    }

    public function testSubmitValidFormWithInvoiceAndFirm()
    {
        $faker = Factory::create();

        $firm = factory(Firm::class)->create(['user_id' => $this->job->user_id]);
        $this->job->firm()->associate($firm);

        $this->job->save();

        $payment = $this->job->getUnpaidPayment();

        $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price'          => $payment->plan->gross_price,
                'invoice'        => [
                    'name'        => $firm->name,
                    'vat_id'      => $vat = '8943139460',
                    'country_id'  => $this->poland->id,
                    'address'     => $firm->street,
                    'city'        => $firm->city,
                    'postal_code' => $postalCode = $faker->postcode,
                ],
            ]);

        $firm->refresh();
        $payment->refresh();

        $this->assertEquals($payment->invoice->name, $firm->name);
        $this->assertEquals($payment->invoice->vat_id, $vat);
        $this->assertEquals($payment->invoice->country_id, $this->poland->id);
        $this->assertEquals($payment->invoice->address, $firm->street);
        $this->assertEquals($payment->invoice->city, $firm->city);
        $this->assertEquals($payment->invoice->postal_code, $postalCode);

        $this->assertEquals($this->job->firm->vat_id, $vat);
    }

    public function testSubmitFormWithCoupon()
    {
        $payment = $this->job->getUnpaidPayment();
        $payment->plan_id = Plan::where('name', 'Premium')->value('id');
        $payment->save();

        $coupon = Coupon::create(['amount' => 10, 'code' => $this->faker->word]);

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price'          => $payment->plan->gross_price,
                'coupon'         => $coupon->code,
                'invoice'        => [
                    'name'        => $this->faker->company,
                    'vat_id'      => '8943139460',
                    'country_id'  => $this->poland->id,
                    'address'     => $this->faker->address,
                    'city'        => $this->faker->city,
                    'postal_code' => $this->faker->postcode,
                ],
            ]);

        $response->assertStatus(200);
        $payment->refresh();
        $this->assertEquals($payment->invoice->netPrice(), $payment->plan->price - 10);
    }

    public function testSubmitFormWithTotalDiscount()
    {
        $faker = Factory::create();

        /** @var Plan $plan */
        $plan = Plan::where('name', 'Premium')->first();

        $payment = $this->job->getUnpaidPayment();
        $payment->plan_id = $plan->id;
        $payment->save();

        $coupon = Coupon::create(['amount' => $plan->price, 'code' => $faker->randomAscii]);

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price'          => 0,
                'coupon'         => $coupon->code,
            ],
        );

        $response->assertStatus(201);
        $response->assertSeeText(UrlBuilder::job($this->job));

        $payment->refresh();

        $this->assertEquals($payment->invoice->grossPrice(), 0);
        $this->assertEmpty($payment->invoice->number);
    }

    public function testSubmitFormWithDiscountAndNoCountryId()
    {
        $faker = Factory::create();

        /** @var Plan $plan */
        $plan = Plan::where('name', 'Premium')->first();

        $payment = $this->job->getUnpaidPayment();
        $payment->plan_id = $plan->id;
        $payment->save();

        $coupon = Coupon::create(['amount' => $plan->price, 'code' => $faker->randomAscii]);

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price'          => 0,
                'coupon'         => $coupon->code,
                'invoice'        => [
                    'name'        => $this->faker->company,
                    'vat_id'      => '123123123',
                    'address'     => $this->faker->address,
                    'city'        => $this->faker->city,
                    'postal_code' => $this->faker->postcode,
                ],
            ],
        );

        $response->assertStatus(201);
        $response->assertSeeText(UrlBuilder::job($this->job));

        $payment->refresh();

        $this->assertEquals($payment->invoice->grossPrice(), 0);
        $this->assertEmpty($payment->invoice->number);
    }

    public function testSubmitFreeOfferWithoutVatId()
    {
        /** @var Plan $plan */
        $plan = Plan::where('name', 'Standard')->first();

        $payment = $this->job->getUnpaidPayment();
        $payment->plan_id = $plan->id;
        $payment->save();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price'          => 0,
                'invoice'        => [
                    'name'        => $this->faker->company,
                    'address'     => $this->faker->address,
                    'city'        => $this->faker->city,
                    'postal_code' => $this->faker->postcode,
                ],
            ],
        );

        $response->assertStatus(201);
        $response->assertSeeText(UrlBuilder::job($this->job));

        $payment->refresh();

        $this->assertEquals($payment->invoice->grossPrice(), 0);
        $this->assertEmpty($payment->invoice->number);
    }

    public function testSubmitFreeOfferWithoutCountryId()
    {
        /** @var Plan $plan */
        $plan = Plan::where('name', 'Standard')->first();

        $payment = $this->job->getUnpaidPayment();
        $payment->plan_id = $plan->id;
        $payment->save();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price'          => 0,
                'invoice'        => [
                    'name'        => $this->faker->company,
                    'address'     => $this->faker->address,
                    'city'        => $this->faker->city,
                    'postal_code' => $this->faker->postcode,
                    'vat_id'      => '8943139460',
                ],
            ],
        );

        $response->assertStatus(201);
        $response->assertSeeText(UrlBuilder::job($this->job));

        $payment->refresh();

        $this->assertEquals($payment->invoice->grossPrice(), 0);
        $this->assertEmpty($payment->invoice->number);
    }

    public function testSubmitFreeOfferWithoutValidVatId()
    {
        /** @var Plan $plan */
        $plan = Plan::where('name', 'Standard')->first();

        $payment = $this->job->getUnpaidPayment();
        $payment->plan_id = $plan->id;
        $payment->save();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price'          => 0,
                'invoice'        => [
                    'name'        => $this->faker->company,
                    'address'     => $this->faker->address,
                    'city'        => $this->faker->city,
                    'postal_code' => $this->faker->postcode,
                    'vat_id'      => '12312312',
                ],
            ],
        );

        $response->assertSeeText(UrlBuilder::job($this->job));

        $payment->refresh();

        $this->assertEquals($payment->invoice->grossPrice(), 0);
        $this->assertEmpty($payment->invoice->number);
    }

    public function testSubmitFreeOfferWithoutValidVatIdAndCountryCode()
    {
        /** @var Plan $plan */
        $plan = Plan::where('name', 'Standard')->first();

        $payment = $this->job->getUnpaidPayment();
        $payment->plan_id = $plan->id;
        $payment->save();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price'          => 0,
                'invoice'        => [
                    'name'        => $this->faker->company,
                    'address'     => $this->faker->address,
                    'city'        => $this->faker->city,
                    'postal_code' => $this->faker->postcode,
                    'vat_id'      => '12312312',
                    'country_id'  => $this->poland->id,
                ],
            ],
        );

        $response->assertStatus(201);
        $response->assertSeeText(UrlBuilder::job($this->job));

        $payment->refresh();

        $this->assertEquals($payment->invoice->grossPrice(), 0);
        $this->assertEmpty($payment->invoice->number);
    }

    public function testSubmitFormWithTransferMethod()
    {
        $payment = $this->job->getUnpaidPayment();

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'p24',
                'price'          => $payment->plan->gross_price,
                'invoice'        => [
                    'name'        => $this->faker->company,
                    'vat_id'      => '8943139460',
                    'country_id'  => $this->poland->id,
                    'address'     => $this->faker->address,
                    'city'        => $this->faker->city,
                    'postal_code' => $this->faker->postcode,
                ],
            ],
        );

        $response->assertStatus(200);
    }

    public function testSubmitFormWithZeroRateInvoice()
    {
        $payment = $this->job->getUnpaidPayment();

        $this->assertGreaterThan(0, $payment->plan->gross_price);

        $response = $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price'          => $payment->plan->gross_price,
                'invoice'        => [
                    'name'        => $this->faker->company,
                    'vat_id'      => 'U12345678',
                    'country_id'  => $this->austria->id,
                    'address'     => $this->faker->address,
                    'city'        => $this->faker->city,
                    'postal_code' => $this->faker->postcode,
                ],
            ],
        );

        $response->assertStatus(200);

        $payment->refresh();

        $this->assertEquals($payment->invoice->country_id, $this->austria->id);
        $this->assertEquals($payment->invoice->grossPrice(), $this->job->plan->price);
    }

    public function testSubmitFormToForeignCitizen()
    {
        $payment = $this->job->getUnpaidPayment();
        $this->actingAs($this->job->user)->json(
            'POST',
            "/Praca/Payment/{$payment->id}",
            [
                'payment_method' => 'card',
                'price'          => $payment->plan->gross_price,
                'invoice'        => [
                    'name'        => $this->faker->company,
                    'country_id'  => $this->austria->id,
                    'address'     => $this->faker->address,
                    'city'        => $this->faker->city,
                    'postal_code' => $this->faker->postcode,
                ],
            ],
        );
        $payment->refresh();
        $this->assertEquals($payment->invoice->country_id, $this->austria->id);
        $this->assertEqualsWithDelta($payment->invoice->grossPrice(), $this->job->plan->price * 1.23, 0.00001);
    }
}
