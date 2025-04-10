<?php
namespace Test\Neon2\Integration;

use Neon2\Payment\PaymentGate;
use Neon2\Payment\PaymentStatus;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentGateTest extends TestCase {
    private PaymentGate $paymentGate;

    #[Before]
    public function initialize(): void {
        $this->paymentGate = new PaymentGate();
    }

    #[Test]
    public function storesPayments(): void {
        $this->paymentGate->storePaymentStatus('foo', PaymentStatus::Completed);
        $this->assertSame(PaymentStatus::Completed, $this->paymentGate->readPaymentStatus('foo'));
    }

    #[Test]
    public function ifPaymentIsNotStored_returnsPaymentStatusAwaiting(): void {
        $status = $this->paymentGate->readPaymentStatus('bar');
        $this->assertSame(PaymentStatus::Awaiting, $status);
    }

    #[Test]
    public function updatesPaymentStatus(): void {
        $this->paymentGate->storePaymentStatus('cat', PaymentStatus::Awaiting);
        $this->paymentGate->storePaymentStatus('cat', PaymentStatus::Completed);
        $this->assertSame(PaymentStatus::Completed, $this->paymentGate->readPaymentStatus('cat'));
    }
}
