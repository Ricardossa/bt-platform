<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| BT Queue Platform
|--------------------------------------------------------------------------
| Bootstrap da aplicação
|--------------------------------------------------------------------------
*/

define('BT_START', microtime(true));
define('BT_ROOT', dirname(__DIR__));

// --- GLOBAL CORS FIX PARA APKs ---
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
// ---------------------------------

date_default_timezone_set('America/Bahia');

require_once BT_ROOT . '/bootstrap/autoload.php';

return true;
