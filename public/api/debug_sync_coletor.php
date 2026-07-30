<?php
require_once __DIR__ . '/../../bootstrap/app.php';
use BT\Core\Database\Database;

header('Content-Type: text/plain');

try {
    echo "--- INSTALACOES ---\n";
    $instalacoes = Database::fetchAll("SELECT id, nome, uuid, produto, status FROM instalacoes");
    print_r($instalacoes);

    echo "\n--- LICENCAS ---\n";
    $licencas = Database::fetchAll("SELECT * FROM licencas");
    print_r($licencas);

    echo "\n--- LOGS RECENTES (Ultimos 20) ---\n";
    $logs = Database::fetchAll("SELECT * FROM atividades ORDER BY data_criacao DESC LIMIT 20");
    print_r($logs);

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
