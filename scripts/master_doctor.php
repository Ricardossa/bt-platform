<?php
require_once __DIR__ . '/../bootstrap/app.php';
use BT\Core\Database\Database;

$output = "=== MASTER DOCTOR REPORT ===\n";

function check($label, $sql) {
    global $output;
    $output .= "Check: $label ... ";
    try {
        Database::fetchAll($sql);
        $output .= "OK\n";
    } catch (Exception $e) {
        $output .= "FAILED: " . $e->getMessage() . "\n";
    }
}

check("Table Companies", "SELECT id FROM empresas LIMIT 1");
check("Table Installations", "SELECT id FROM instalacoes LIMIT 1");
check("Table Tenants", "SELECT id FROM tenants LIMIT 1");
check("Table Licenses", "SELECT id FROM licencas LIMIT 1");
check("Table Products", "SELECT id FROM produtos LIMIT 1");

$output .= "\n--- STRUCTURES ---\n";
$tables = ['empresas', 'instalacoes', 'tenants', 'licencas', 'produtos'];
foreach($tables as $t) {
    $output .= "Table: $t\n";
    try {
        $cols = Database::fetchAll("DESCRIBE $t");
        foreach($cols as $c) {
            $output .= "  - {$c['Field']} ({$c['Type']})\n";
        }
    } catch (Exception $e) {
        $output .= "  Error: " . $e->getMessage() . "\n";
    }
}

file_put_contents(__DIR__ . '/doctor_report.txt', $output);
echo "Report generated in Y:/bt-platform/scripts/doctor_report.txt";
