<?php
namespace Neon2\JobBoard;

use Neon2\Database;
use Neon2\Request\JobOfferSubmit;

readonly class JobBoardGate {
    private Database $database;

    public function __construct() {
        $this->database = new Database();
        $this->database->execute('CREATE TABLE IF NOT EXISTS job_offers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            fields TEXT NOT NULL,
            duration INTEGER NOT NULL,
            pricingPlan TEXT NOT NULL,
            status TEXT NOT NULL,
            paymentId TEXT);');
    }

    public function createJobOffer(
        JobOfferSubmit $jobOffer,
        string         $pricingPlan,
        ?string        $paymentId,
    ): JobOffer {
        $record = new JobOffer(0,
            $pricingPlan === 'free' ? 14 : 30,
            $pricingPlan === 'free' ? JobOfferStatus::Published : JobOfferStatus::AwaitingPayment,
            $paymentId,
            $jobOffer);
        $id = $this->insertJobOffer($record, $pricingPlan);
        $record->id = $id;
        return $record;
    }

    public function updateJobOffer(int $jobOfferId, JobOfferSubmit $jobOffer): void {
        $this->database->execute('UPDATE job_offers SET fields = :fields WHERE id = :id;', [
            'id'     => $jobOfferId,
            'fields' => \serialize($jobOffer),
        ]);
    }

    public function publishJobOffer(int $jobOfferId): void {
        $this->database->execute('UPDATE job_offers SET status = :status WHERE id = :id;', [
            'id'     => $jobOfferId,
            'status' => $this->format(JobOfferStatus::Published),
        ]);
    }

    /**
     * @return JobOffer[]
     */
    public function listJobOffers(): array {
        return array_map(fn(array $row) => new JobOffer(
            $row['id'],
            $row['duration'],
            $this->parse($row['status']),
            $row['paymentId'],
            \unserialize($row['fields']),
        ),
            $this->database->query('SELECT id, fields, duration, status, paymentId FROM job_offers;'));
    }

    public function clear(): void {
        $this->database->execute('DELETE from job_offers;');
    }

    private function insertJobOffer(JobOffer $jobOffer, string $pricingPlan): int {
        return $this->database->insert('INSERT INTO job_offers (fields, duration, pricingPlan, status, paymentId) 
            VALUES (:fields, :duration, :pricingPlan, :status, :paymentId);', [
            'fields'      => \serialize($jobOffer->fields),
            'duration'    => $jobOffer->expiresInDays,
            'pricingPlan' => $pricingPlan,
            'status'      => $this->format($jobOffer->status),
            'paymentId'   => $jobOffer->paymentId,
        ]);
    }

    private function format(JobOfferStatus $status): string {
        return $status->value;
    }

    private function parse(string $status): JobOfferStatus {
        return JobOfferStatus::from($status);
    }

    public function jobOfferIdByPaymentId(string $paymentId): int {
        $records = $this->database->query('SELECT id FROM job_offers WHERE paymentId = :paymentId;', ['paymentId' => $paymentId]);
        return $records[0]['id'];
    }

    public function pricingPlanByPaymentId(string $paymentId): string {
        $records = $this->database->query('SELECT pricingPlan FROM job_offers WHERE paymentId = :paymentId;', ['paymentId' => $paymentId]);
        return $records[0]['pricingPlan'];
    }
}
