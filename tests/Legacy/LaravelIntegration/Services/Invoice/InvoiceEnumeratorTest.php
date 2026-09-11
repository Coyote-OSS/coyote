<?php

namespace Tests\Legacy\LaravelIntegration\Services\Invoice;

use Coyote\Invoice;
use Coyote\Repositories\Contracts\InvoiceRepositoryInterface;
use Coyote\Services\Invoice\Enumerator;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class InvoiceEnumeratorTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var \Coyote\User
     */
    protected $user;

    /**
     * @var \Coyote\Services\Invoice\Enumerator
     */
    private $enumerator;

    protected function setUp(): void
    {
        parent::setUp();

        $repository = $this->app[InvoiceRepositoryInterface::class];
        $this->enumerator = new Enumerator($repository);

        $this->user = factory(User::class)->create();
    }

    public function testEnumerateFirstInvoice()
    {
        $faker = Factory::create();

        $invoice = new Invoice(['name' => $faker->company, 'user_id' => $this->user->id]);

        $invoice = $this->enumerator->enumerate($invoice);
        $date = now();

        $this->assertEquals($this->format($date, 1), $invoice->number);
        $this->assertEquals($date->hour, $invoice->created_at->hour);
    }

    public function testEnumerateMultipleInvoice()
    {
        $faker = Factory::create();
        $date = now()->today();

        $invoice = Invoice::forceCreate(['name' => $faker->company, 'number' => $this->format($date, 1),  'user_id' => $this->user->id]);

        $invoice = $this->enumerator->enumerate($invoice);

        $this->enumerator->enumerate($invoice);

        $this->assertEquals($this->format($date, 2), $invoice->number);
    }

    private function format($date, $seq)
    {
        return sprintf('%02d%02d%02d-%d', $date->year, $date->month, $date->day, $seq);
    }
}
