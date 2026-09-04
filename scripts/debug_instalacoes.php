<?php
require_once __DIR__ . '/../bootstrap/app.php';
use BT\Core\Database\Database;

try {
    echo "--- TOTAL EM INSTALACOES ---\n";
    $total = Database::fetch("SELECT COUNT(*) as total FROM instalacoes");
    echo "Total: " . $total['total'] . "\n\n";

    echo "--- LISTA COMPLETA (SEM JOINS) ---\n";
    $rows = Database::fetchAll("SELECT id, nome, produto, empresa_id FROM instalacoes");
    foreach($rows as $r) {
        echo "ID: {$r['id']} | Nome: {$r['nome']} | Produto: {$r['produto']} | Empresa ID: {$r['empresa_id']}\n";
    }

    echo "\n--- VERIFICANDO EMPRESAS ---\n";
    $empresas = Database::fetchAll("SELECT id, nome_fantasia FROM empresas");
    print_r($empresas);

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
