<?php
namespace Neon2;

use PDO;

class Database {
    private PDO $pdo;

    public function __construct() {
        $file = __DIR__ . '/../database.db';
        $this->pdo = new \PDO("sqlite:$file");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function query(string $query, array $arguments = []): array {
        $statement = $this->pdo->prepare($query);
        $statement->execute($arguments);
        return $statement->fetchAll();
    }

    public function insert(string $query, array $arguments): int {
        $this->execute($query, $arguments);
        return (int)$this->pdo->lastInsertId();
    }

    public function execute(string $query, array $arguments = []): void {
        $this->pdo->prepare($query)->execute($arguments);
    }
}
