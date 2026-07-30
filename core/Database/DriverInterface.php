<?php

declare(strict_types=1);

namespace BT\Core\Database;

interface DriverInterface
{
    public function connect(): void;

    public function disconnect(): void;

    public function query(
        string $sql,
        array $params = []
    ): mixed;

    public function fetch(
        string $sql,
        array $params = []
    ): ?array;

    public function fetchAll(
        string $sql,
        array $params = []
    ): array;

    public function execute(
        string $sql,
        array $params = []
    ): bool;

    public function lastInsertId(): string;

    public function beginTransaction(): bool;

    public function commit(): bool;

    public function rollBack(): bool;

    public function isConnected(): bool;
}
