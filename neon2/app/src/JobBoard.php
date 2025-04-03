<?php
namespace Neon2;

use PDO;

readonly class JobBoard
{
    private PDO $pdo;

    public function __construct()
    {
        $file = __DIR__ . '/jobBoard.dat';
        $this->pdo = new \PDO("sqlite:$file");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS job_offers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            duration INTEGER NOT NULL
        )');
    }

    public function addJobOffer(string $title, string $plan): JobOffer
    {
        $duration = $plan === 'free' ? 14 : 30;
        return new JobOffer(
            $this->insertJobOffer($title, $duration),
            $title,
            $duration);
    }

    public function editJobOffer(int $jobOfferId, string $jobOfferTitle): void
    {
        $this->execute('UPDATE job_offers SET title = :title WHERE id = :id;', [
            'id'    => $jobOfferId,
            'title' => $jobOfferTitle,
        ]);
    }

    /**
     * @return JobOffer[]
     */
    public function listJobOffers(): array
    {
        return array_map(
            fn(array $row) => new JobOffer($row['id'], $row['title'], $row['duration']),
            $this->query('SELECT id, title, duration FROM job_offers;'));
    }

    public function clear(): void
    {
        $this->query('DELETE from job_offers');
    }

    private function insertJobOffer(string $title, int $duration): int
    {
        return $this->insert('INSERT INTO job_offers (title, duration) VALUES (:title, :duration);', [
            'title'    => $title,
            'duration' => $duration,
        ]);
    }

    private function query(string $query, array $arguments = []): array
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($arguments);
        return $statement->fetchAll();
    }

    private function insert(string $query, array $arguments): int
    {
        $this->execute($query, $arguments);
        return (int)$this->pdo->lastInsertId();
    }

    private function execute(string $query, array $arguments): void
    {
        $this->pdo->prepare($query)->execute($arguments);
    }
}
