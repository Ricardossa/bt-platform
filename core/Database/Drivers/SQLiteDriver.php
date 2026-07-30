<?php

declare(strict_types=1);

namespace BT\Core\Database\Drivers;

use BT\Core\Database\DriverInterface;
use PDO;
use PDOStatement;

final class SQLiteDriver implements DriverInterface
{
    private array $config;

    private ?PDO $pdo = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function connect(): void
    {
        if ($this->pdo !== null) {
            return;
        }

        $this->pdo = new PDO(
            'sqlite:' . $this->config['database']
        );

        $this->pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }

    public function disconnect(): void
    {
        $this->pdo = null;
    }

    public function query(
        string $sql,
        array $params = []
    ): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return $stmt;
    }

    public function fetch(
        string $sql,
        array $params = []
    ): ?array
    {
        $result = $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function fetchAll(
        string $sql,
        array $params = []
    ): array
    {
        return $this->query($sql, $params)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function execute(
        string $sql,
        array $params = []
    ): bool
    {
        return $this->query($sql, $params) !== false;
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }
}
