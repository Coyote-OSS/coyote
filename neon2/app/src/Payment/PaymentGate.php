<?php
namespace Neon2\Payment;

use Neon2\Database;

readonly class PaymentGate {
    private Database $database;

    public function __construct() {
        $this->database = new Database();
        $this->database->execute('CREATE TABLE IF NOT EXISTS payments (
            paymentId TEXT NOT NULL UNIQUE,
            status TEXT NOT NULL);');
    }

    public function storePaymentStatus(string $paymentId, PaymentStatus $status): void {
        $this->database->execute('INSERT INTO payments (paymentId, status)
            VALUES (:paymentId, :status)
            ON CONFLICT(paymentId) DO UPDATE SET status = excluded.status;', [
            'paymentId' => $paymentId,
            'status'    => $this->format($status),
        ]);
    }

    public function readPaymentStatus(string $paymentId): PaymentStatus {
        $values = $this->database->query('SELECT status FROM payments WHERE paymentId = :paymentId;', [
            'paymentId' => $paymentId,
        ]);
        if (empty($values)) {
            return PaymentStatus::Awaiting;
        }
        return $this->parse($values[0]['status']);
    }

    private function format(PaymentStatus $status): string {
        return match ($status) {
            PaymentStatus::Completed => 'completed',
            PaymentStatus::Failed    => 'failed',
            PaymentStatus::Awaiting  => 'awaiting',
        };
    }

    private function parse(string $status): PaymentStatus {
        return match ($status) {
            'completed' => PaymentStatus::Completed,
            'failed'    => PaymentStatus::Failed,
            'awaiting'  => PaymentStatus::Awaiting,
        };
    }
}
