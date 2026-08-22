<?php
require_once __DIR__ . '/../bootstrap/app.php';
use BT\Core\Database\Database;

header('Content-Type: text/plain');

try {
    echo "=== PENDING ACTIVATIONS ===\n";
    $rows = Database::fetchAll("SELECT id, nome, codigo_ativacao, expiracao_ativacao FROM instalacoes WHERE codigo_ativacao IS NOT NULL");
    print_r($rows);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
