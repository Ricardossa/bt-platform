<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\Core\Database\Database;

header('Content-Type: text/plain');

try {
    echo "--- INSTALAÇÕES E EMPRESAS ---\n";
    $res = Database::fetchAll("
        SELECT i.id, i.nome as instalacao_nome, e.nome_fantasia as empresa_nome, i.uuid
        FROM instalacoes i
        LEFT JOIN empresas e ON e.id = i.empresa_id
    ");
    print_r($res);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
