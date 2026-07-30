<?php
require_once __DIR__ . '/../../bootstrap/app.php';
use BT\Core\Database\Database;
header('Content-Type: text/plain');
try {
    echo "--- INSTALACOES OFFLINE (Sem sync nas ultimas 12h) ---\n";
    $res = Database::fetchAll("SELECT id, nome, produto, status, ultima_sincronizacao FROM instalacoes WHERE ultima_sincronizacao < NOW() - INTERVAL 12 HOUR OR ultima_sincronizacao IS NULL");
    print_r($res);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
