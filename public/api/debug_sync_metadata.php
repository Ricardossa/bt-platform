<?php
require_once __DIR__ . '/../../bootstrap/app.php';
use BT\Core\Database\Database;
header('Content-Type: text/plain');
try {
    $logs = Database::fetchAll("SELECT id, categoria, mensagem, metadata, data_criacao FROM atividades WHERE categoria = 'SYNC' ORDER BY data_criacao DESC LIMIT 10");
    print_r($logs);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
