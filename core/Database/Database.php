<?php

declare(strict_types=1);

namespace BT\Core\Database;

final class Database
{
    private static ?DriverInterface $driver = null;

    private static function driver(): DriverInterface
    {
        if (self::$driver === null) {
            self::$driver = DriverFactory::create();
            self::$driver->connect();
        }

        return self::$driver;
    }

    public static function query(
        string $sql,
        array $params = []
    ): mixed {
        return self::driver()->query($sql, $params);
    }

    public static function fetch(
        string $sql,
        array $params = []
    ): ?array {
        return self::driver()->fetch($sql, $params);
    }

    public static function fetchAll(
        string $sql,
        array $params = []
    ): array {
        return self::driver()->fetchAll($sql, $params);
    }

    public static function execute(
        string $sql,
        array $params = []
    ): bool {
        return self::driver()->execute($sql, $params);
    }

    public static function beginTransaction(): bool
    {
        return self::driver()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::driver()->commit();
    }

    public static function rollBack(): bool
    {
        return self::driver()->rollBack();
    }

    public static function lastInsertId(): string
    {
        return self::driver()->lastInsertId();
    }

    public static function isConnected(): bool
    {
        return self::driver()->isConnected();
    }

    public static function disconnect(): void
    {
        self::driver()->disconnect();
        self::$driver = null;
    }
}
