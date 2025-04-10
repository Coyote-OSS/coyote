<?php
namespace Test\Neon2\Integration;

use Neon2\Payment\Provider\TestPaymentGate;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TestPaymentGateTest extends TestCase {
    private TestPaymentGate $testPayments;

    #[Before]
    public function initialize(): void {
        $this->testPayments = new TestPaymentGate();
    }

    #[Test]
    public function storesPayments(): void {
        $this->testPayments->addPaymentId('payment-token', 'payment-id');
        $this->assertSame('payment-id',
            $this->testPayments->fetchPaymentId('payment-token'));
    }
}
