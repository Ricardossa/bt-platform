<?php
header('Content-Type: application/json; charset=utf-8');
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=bt_enterprise_saas", 'bt_saas_user', 'BrandaoElite2026!');

    // Configurações Base para o Tenant 1
    $configs = [
        ['empresa', 'Brandão Tech Enterprise'],
        ['label_cliente', 'Paciente'],
        ['uuid', 'LITE-DIAMOND-001'],
        ['whatsapp_enabled', '0'],
        ['priority_mode', 'STRICT'],
        ['priority_ratio', '3']
    ];

    foreach ($configs as $c) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO configuracoes (tenant_id, chave, valor) VALUES (1, ?, ?)");
        $stmt->execute($c);
    }

    echo json_encode(['success' => true, 'message' => 'Configurações de base injetadas.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
