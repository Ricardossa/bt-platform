<?php
require_once __DIR__ . '/../../bootstrap.php';
use BT\Core\Database\Database;

header('Content-Type: application/json');

try {
    $uuid = '5579fd18-b48e-473e-a6e1-fb3cf1335700';
    $instalacao = Database::fetch("SELECT id, nome, produto FROM instalacoes WHERE uuid = ?", [$uuid]);

    if (!$instalacao) {
        die(json_encode(['error' => 'Instalação não encontrada']));
    }

    $id = $instalacao['id'];
    $dispositivos = Database::fetchAll("SELECT * FROM dispositivos WHERE instalacao_id = ?", [$id]);

    // Busca se o device_uuid do Lite está em outra instalacao
    $hostname = gethostname();
    $globalDevice = Database::fetch("SELECT * FROM dispositivos WHERE device_uuid = ?", [$hostname]);

    echo json_encode([
        'instalacao' => $instalacao,
        'dispositivos_vinculados' => $dispositivos,
        'global_device_search' => [
            'hostname_searched' => $hostname,
            'found' => $globalDevice
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
