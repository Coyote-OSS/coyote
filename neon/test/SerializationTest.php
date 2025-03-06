<?php
namespace Neon\Test;

use Neon\View\Currency;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SerializationTest extends TestCase
{
    #[Test]
    public function currencyIsSerialized(): void
    {
        $this->assertSame('"EUR"', $this->serialize(Currency::EUR));
    }

    private function serialize(Currency $currency): string
    {
        return \json_encode($currency, \JSON_THROW_ON_ERROR);
    }
}
