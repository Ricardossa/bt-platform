<?php

declare(strict_types=1);

namespace BT\Core;

final class Logger
{
    private static string $logDir = BT_ROOT . '/storage/logs';

    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    public static function warning(string $message): void
    {
        self::write('WARNING', $message);
    }

    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }

    public static function debug(string $message): void
    {
        self::write('DEBUG', $message);
    }

    private static function write(string $level, string $message): void
    {
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0775, true);
        }

        $file = self::$logDir . '/' . date('Y-m-d') . '.log';

        $line = sprintf(
            "[%s] [%s] %s%s",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            PHP_EOL
        );

        file_put_contents(
            $file,
            $line,
            FILE_APPEND | LOCK_EX
        );
    }

    public static function clearToday(): void
    {
        $file = self::$logDir . '/' . date('Y-m-d') . '.log';

        if (file_exists($file)) {
            unlink($file);
        }
    }
}
