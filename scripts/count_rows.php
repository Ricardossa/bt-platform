<?php
require_once __DIR__ . '/../bootstrap/app.php';
use BT\Core\Database\Database;

try {
    $tables = ['empresas', 'instalacoes', 'licencas', 'dispositivos', 'tenants', 'atividades'];
    echo "--- ROW COUNTS ---\n";
    foreach ($tables as $t) {
        try {
            $res = Database::fetch("SELECT COUNT(*) as total FROM $t");
            echo "$t: " . ($res['total'] ?? 0) . "\n";
        } catch (Exception $e) {
            echo "$t: ERROR (" . $e->getMessage() . ")\n";
        }
    }
} catch (Exception $e) {
    echo "ERRO GERAL: " . $e->getMessage() . "\n";
}
