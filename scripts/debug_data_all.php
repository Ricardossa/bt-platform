<?php
require_once __DIR__ . '/../bootstrap/app.php';
use BT\Core\Database\Database;

try {
    echo "=== INSTALACOES ===\n";
    $instalacoes = Database::fetchAll("SELECT id, nome, empresa_id, produto FROM instalacoes");
    foreach($instalacoes as $i) {
        echo "ID: {$i['id']} | Nome: {$i['nome']} | Empresa: {$i['empresa_id']} | Produto: {$i['produto']}\n";
    }

    echo "\n=== TENANTS ===\n";
    $tenants = Database::fetchAll("SELECT id, slug, nome FROM tenants");
    foreach($tenants as $t) {
        echo "ID: {$t['id']} | Slug: {$t['slug']} | Nome: {$t['nome']}\n";
    }

    echo "\n=== EMPRESAS ===\n";
    $empresas = Database::fetchAll("SELECT id, nome_fantasia FROM empresas");
    foreach($empresas as $e) {
        echo "ID: {$e['id']} | Nome: {$e['nome_fantasia']}\n";
    }

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
