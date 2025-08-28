<?php

namespace App\Repository;

use PDO;

class AccountRepository
{
    private PDO $pdo;
    private string $dbFile;

    public function __construct(string $dbFile)
    {
        $this->dbFile = $dbFile;
        $this->pdo = new PDO('sqlite:' . $this->dbFile);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTable();
    }

    private function createTable(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS accounts (id TEXT PRIMARY KEY, balance INTEGER)");
    }

    public function reset(): void
    {
        if (file_exists($this->dbFile)) {
            unlink($this->dbFile);
        }
        // Recreate the PDO connection and table for subsequent requests in the same run
        $this->pdo = new PDO('sqlite:' . $this->dbFile);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTable();
    }

    public function findById(string $accountId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, balance FROM accounts WHERE id = ?");
        $stmt->execute([$accountId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function createOrUpdate(string $accountId, int $balance): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO accounts (id, balance) VALUES (?, ?)
             ON CONFLICT(id) DO UPDATE SET balance = excluded.balance"
        );
        $stmt->execute([$accountId, $balance]);
    }
}
