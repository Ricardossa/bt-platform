<?php
/**
 * API DE DESCOBERTA BT QUEUE (v1.1.0)
 * Localiza a unidade Lite baseada no UUID da instalação.
 */
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\Core\Database\Database;

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

$uuid = $_GET['uuid'] ?? '';

if (empty($uuid)) {
    die(json_encode(['success' => false, 'message' => 'UUID da unidade obrigatorio.']));
}

try {
    // Busca a instalação pelo UUID
    $sql = "SELECT i.ultimo_ip as ip_externo, i.ip_local, i.url_acesso, i.status, i.versao
            FROM instalacoes i
            WHERE i.uuid = ?
            LIMIT 1";

    $instalacao = Database::fetch($sql, [$uuid]);

    if (!$instalacao) {
        die(json_encode(['success' => false, 'message' => 'Unidade nao encontrada na plataforma.']));
    }

    $ipLocal = $instalacao['ip_local'] ?: 'localhost';

    echo json_encode([
        'success' => true,
        'data' => [
            'status' => $instalacao['status'],
            'versao' => $instalacao['versao'],
            'url_externa' => $instalacao['url_acesso'] ?: "http://" . $instalacao['ip_externo'] . ":8120/",
            'url_local' => "http://" . $ipLocal . ":8120/",
            'last_seen' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
