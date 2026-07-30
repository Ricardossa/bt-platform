<?php
require_once __DIR__ . '/../../bootstrap/app.php';
use BT\Core\Database\Database;
header('Content-Type: text/plain');
try {
    echo "--- DEVICES ---\n";
    $devices = Database::fetchAll("SELECT * FROM dispositivos ORDER BY ultima_sincronizacao DESC LIMIT 10");
    print_r($devices);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
