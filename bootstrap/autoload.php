<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| BT Queue Platform
|--------------------------------------------------------------------------
| Autoloader da aplicação
|--------------------------------------------------------------------------
*/

spl_autoload_register(function (string $class): void {

    $prefix = 'BT\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));

    $parts = explode('\\', $relative);
    
    $parts[0] = strtolower($parts[0]);

    $file = BT_ROOT . '/'
        . implode('/', $parts)
        . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});
