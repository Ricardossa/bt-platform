<?php
require_once __DIR__ . '/../../bootstrap/app.php';
use BT\Core\Database\Database;
header('Content-Type: text/plain');
try {
    $res = Database::fetchAll("SELECT id, nome, produto, status, ultima_sincronizacao FROM instalacoes");
    echo "TOTAL: " . count($res) . "\n\n";
    print_r($res);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
