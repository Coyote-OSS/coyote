<?php
namespace Neon2\JobBoard;

use Neon2\Database;

readonly class JobBoardGate {
    private Database $database;

    public function __construct() {
        $this->database = new Database();
        $this->database->execute('CREATE TABLE IF NOT EXISTS job_offers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            duration INTEGER NOT NULL,
            status TEXT NOT NULL);');
    }

    public function addJobOffer(string $title, string $plan): JobOffer {
        $jobOffer = new JobOffer(0,
            $title,
            $plan === 'free' ? 14 : 30,
            $plan === 'free' ? JobOfferStatus::Published : JobOfferStatus::AwaitingPayment);
        $id = $this->insertJobOffer($jobOffer);
        $jobOffer->id = $id;
        return $jobOffer;
    }

    public function editJobOffer(int $jobOfferId, string $jobOfferTitle): void {
        $this->database->execute('UPDATE job_offers SET title = :title WHERE id = :id;', [
            'id'    => $jobOfferId,
            'title' => $jobOfferTitle,
        ]);
    }

    public function payJobOfferPayment(int $jobOfferId): void {
        $this->database->execute('UPDATE job_offers SET status = :status WHERE id = :id;', [
            'id'     => $jobOfferId,
            'status' => $this->format(JobOfferStatus::Published),
        ]);
    }

    /**
     * @return JobOffer[]
     */
    public function listJobOffers(): array {
        return array_map(
            fn(array $row) => new JobOffer($row['id'], $row['title'], $row['duration'], $this->parse($row['status'])),
            $this->database->query('SELECT id, title, duration, status FROM job_offers;'));
    }

    public function clear(): void {
        $this->database->query('DELETE from job_offers');
    }

    private function insertJobOffer(JobOffer $jobOffer): int {
        return $this->database->insert('INSERT INTO job_offers (title, duration, status) VALUES (:title, :duration, :status);', [
            'title'    => $jobOffer->title,
            'duration' => $jobOffer->expiresInDays,
            'status'   => $this->format($jobOffer->status),
        ]);
    }

    private function format(JobOfferStatus $status): string {
        return $status->value;
    }

    private function parse(string $status): JobOfferStatus {
        return JobOfferStatus::from($status);
    }
}
