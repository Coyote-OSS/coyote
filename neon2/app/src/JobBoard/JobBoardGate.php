<?php
namespace Neon2\JobBoard;

use PDO;

readonly class JobBoardGate {
    private PDO $pdo;

    public function __construct() {
        $file = __DIR__ . '/jobBoard.dat';
        $this->pdo = new \PDO("sqlite:$file");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS job_offers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            duration INTEGER NOT NULL,
            status TEXT NOT NULL
        )');
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
        $this->execute('UPDATE job_offers SET title = :title WHERE id = :id;', [
            'id'    => $jobOfferId,
            'title' => $jobOfferTitle,
        ]);
    }

    public function payJobOfferPayment(int $jobOfferId): void {
        $this->execute('UPDATE job_offers SET status = :status WHERE id = :id;', [
            'id'     => $jobOfferId,
            'status' => $this->toSql(JobOfferStatus::Published),
        ]);
    }

    /**
     * @return JobOffer[]
     */
    public function listJobOffers(): array {
        return array_map(
            fn(array $row) => new JobOffer($row['id'], $row['title'], $row['duration'], $this->fromSql($row['status'])),
            $this->query('SELECT id, title, duration, status FROM job_offers;'));
    }

    public function clear(): void {
        $this->query('DELETE from job_offers');
    }

    private function insertJobOffer(JobOffer $jobOffer): int {
        return $this->insert('INSERT INTO job_offers (title, duration, status) VALUES (:title, :duration, :status);', [
            'title'    => $jobOffer->title,
            'duration' => $jobOffer->expiresInDays,
            'status'   => $this->toSql($jobOffer->status),
        ]);
    }

    private function query(string $query, array $arguments = []): array {
        $statement = $this->pdo->prepare($query);
        $statement->execute($arguments);
        return $statement->fetchAll();
    }

    private function insert(string $query, array $arguments): int {
        $this->execute($query, $arguments);
        return (int)$this->pdo->lastInsertId();
    }

    private function execute(string $query, array $arguments): void {
        $this->pdo->prepare($query)->execute($arguments);
    }

    private function toSql(JobOfferStatus $status): string {
        return $status->value;
    }

    private function fromSql(string $status): JobOfferStatus {
        return JobOfferStatus::from($status);
    }
}
