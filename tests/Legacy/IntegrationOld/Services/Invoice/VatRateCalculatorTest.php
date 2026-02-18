<?php
namespace Tests\Legacy\IntegrationOld\Services\Invoice;

use Coyote\Country;
use Coyote\Services\Invoice\VatRateCalculator;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel;

class VatRateCalculatorTest extends TestCase
{
    use Laravel\Application;
    use Laravel\Transactional;

    private VatRateCalculator $calc;

    #[Before]
    public function initialize(): void
    {
        $this->calc = new VatRateCalculator();
    }

    #[Test]
    public function defaultRatesWithoutCountry(): void
    {
        $this->assertEquals(1.23, $this->calc->vatRate(null, null));
        $this->assertEquals(1.23, $this->calc->vatRate(null, '894-313-94-36'));
    }

    #[Test]
    public function defaultRatesWithoutVatId()
    {
        $this->assertEquals(1.23, $this->calc->vatRate($this->austria(), null));
        $this->assertEquals(1.23, $this->calc->vatRate($this->austria(), ''));
    }

    #[Test]
    public function vatRateForPoland()
    {
        $this->assertEquals(1.23, $this->calc->vatRate($this->poland(), '894-313-94-36'));
    }

    #[Test]
    public function vatRateForAustria()
    {
        $this->assertEquals(1, $this->calc->vatRate($this->austria(), '894-313-94-36'));
    }

    private function poland(): Country
    {
        return $this->country('Polska', 'PL', 1.23);
    }

    private function austria(): Country
    {
        return $this->country('Austria', 'AT', 1);
    }

    private function country(string $name, string $code, float $vatRate): Country
    {
        return $this->firstOrForceCreate([
            'name'     => $name,
            'code'     => $code,
            'vat_rate' => $vatRate,
        ]);
    }

    private function firstOrForceCreate(array $attributes): Country
    {
        return Country::query()->where($attributes)->first() ?? Country::query()->forceCreate($attributes);
    }
}
