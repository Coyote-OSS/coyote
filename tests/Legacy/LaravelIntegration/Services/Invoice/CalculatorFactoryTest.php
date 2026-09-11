<?php

namespace Tests\Legacy\LaravelIntegration\Services\Invoice;

use Coyote\Country;
use Coyote\Job;
use Coyote\Payment;
use Coyote\Services\Invoice\CalculatorFactory;
use Tests\Legacy\LaravelIntegration\TestCase;

class CalculatorFactoryTest extends TestCase
{
    public function testMakeCalculatorFromPolishPayment()
    {
        $job = factory(Job::class)->states(['firm', 'id'])->make();
        /** @var Payment $payment */
        $payment = factory(Payment::class)->make();

        $payment->job()->associate($job);

        $calculator = CalculatorFactory::payment($payment);

        $this->assertEquals(1.23, $calculator->vatRate);
    }

    public function testMakeCalculatorFromPolishPaymentWithoutFirm()
    {
        $job = factory(Job::class)->states(['id'])->make();
        /** @var Payment $payment */
        $payment = factory(Payment::class)->make();

        $payment->job()->associate($job);

        $calculator = CalculatorFactory::payment($payment);

        $this->assertEquals(1.23, $calculator->vatRate);
    }

    public function testMakeCalculatorFromEuPaymentWithoutVatId()
    {
        $austria = Country::where('name', 'Austria')->first();

        $this->assertNotEmpty($austria);
        $this->assertEquals(1, $austria->vat_rate);

        $job = factory(Job::class)->states(['firm', 'id'])->make();
        $job->firm->country_id = $austria->id;

        /** @var Payment $payment */
        $payment = factory(Payment::class)->make();

        $payment->job()->associate($job);

        $calculator = CalculatorFactory::payment($payment);

        // no VAT ID was provided...
        $this->assertEquals(1.23, $calculator->vatRate);
    }

    public function testMakeCalculatorFromEuPaymentWithVatId()
    {
        $austria = Country::where('name', 'Austria')->first();

        $this->assertNotEmpty($austria);
        $this->assertEquals(1, $austria->vat_rate);

        $job = factory(Job::class)->states(['firm', 'id'])->make();
        $job->firm->country_id = $austria->id;
        $job->firm->vat_id = '12312312321';

        /** @var Payment $payment */
        $payment = factory(Payment::class)->make();

        $payment->job()->associate($job);

        $calculator = CalculatorFactory::payment($payment);

        $this->assertEquals(1, $calculator->vatRate);
    }
}
