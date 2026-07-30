<?php

declare(strict_types=1);

namespace BT\Core\Database;

use BT\Core\Database\Drivers\SQLiteDriver;
use BT\Core\Database\Drivers\MariaDBDriver;

final class DriverFactory
{
    public static function create(): DriverInterface
    {
        $config = require BT_ROOT . '/config/database.php';

        return match ($config['driver']) {

            'sqlite' => new SQLiteDriver(
                $config['sqlite']
            ),

            'mariadb' => new MariaDBDriver(
                $config['mariadb']
            ),

            default => throw new \RuntimeException(
                'Driver de banco não suportado.'
            ),

        };
    }
}
