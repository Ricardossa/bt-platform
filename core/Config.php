<?php

declare(strict_types=1);

namespace BT\Core;

final class Config
{
    private static array $items = [];

    public static function set(string $key, mixed $value): void
    {
        self::$items[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$items[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$items);
    }
}
