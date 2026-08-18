<?php
header('Content-Type: application/json');
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=bt_enterprise_saas", 'bt_saas_user', 'BrandaoElite2026!');
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $tenants = $pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
    echo json_encode(['success' => true, 'tables' => $tables, 'tenants' => $tenants]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
