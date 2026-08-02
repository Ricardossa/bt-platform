<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\Core\Database\Database;

header('Content-Type: text/plain');

try {
    echo "--- TODAS AS INSTALAÇÕES NA MASTER ---\n";
    $insts = Database::fetchAll("SELECT id, nome, uuid FROM instalacoes ORDER BY id DESC");
    print_r($insts);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
