<?php
namespace Neon2\Payment\Provider;

use Neon2\Database;

readonly class TestPaymentGate {
    private Database $database;

    public function __construct() {
        $this->database = new Database();

        $this->database->execute('CREATE TABLE IF NOT EXISTS test_payments (
            paymentToken TEXT NOT NULL,
            paymentId TEXT NOT NULL);');
    }

    public function addPaymentId(string $paymentToken, string $paymentId): void {
        $this->database->execute('INSERT INTO test_payments (paymentToken, paymentId) 
            VALUES (:paymentToken, :paymentId);', [
            'paymentToken' => $paymentToken,
            'paymentId'    => $paymentId,
        ]);
    }

    public function fetchPaymentId(string $paymentToken): string {
        $values = $this->database->query('SELECT paymentId FROM test_payments 
            WHERE paymentToken = :paymentToken;', [
            'paymentToken' => $paymentToken,
        ]);
        return $values[0]['paymentId'];
    }
}
