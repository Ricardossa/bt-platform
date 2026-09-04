<?php
require_once __DIR__ . '/../bootstrap/app.php';
use BT\Core\Database\Database;

try {
    echo "--- INSTALACOES SCHEMA ---\n";
    $cols = Database::fetchAll("DESCRIBE instalacoes");
    print_r($cols);

    echo "\n--- TENANTS SCHEMA ---\n";
    $cols = Database::fetchAll("DESCRIBE tenants");
    print_r($cols);

    echo "\n--- LICENCAS SCHEMA ---\n";
    $cols = Database::fetchAll("DESCRIBE licencas");
    print_r($cols);

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
