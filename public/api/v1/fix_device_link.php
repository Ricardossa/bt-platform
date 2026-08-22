<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/../../bootstrap/app.php';
require_once __DIR__ . '/../../bootstrap/autoload.php';
use BT\Core\Database\Database;

try {
    $hostname = 'bt-platform-lab';
    $liteInstalacaoId = 40; // VM - BANCADA

    echo "Iniciando correção de vínculo para o dispositivo: $hostname\n";

    // 1. Busca o dispositivo
    $device = Database::fetch("SELECT id, instalacao_id FROM dispositivos WHERE device_uuid = ?", [$hostname]);

    if (!$device) {
        echo "Dispositivo não encontrado. Ele será criado no próximo pulso da Lite.\n";
    } else {
        echo "Dispositivo ID {$device['id']} estava vinculado à instalação ID {$device['instalacao_id']}.\n";

        // 2. Transfere o vínculo para a Lite
        $ok = Database::execute("UPDATE dispositivos SET instalacao_id = ?, ativo = 1, status = 'ONLINE' WHERE id = ?", [$liteInstalacaoId, $device['id']]);

        if ($ok) {
            echo "Vínculo corrigido com sucesso! Agora o dispositivo pertence à 'VM - BANCADA'.\n";
        } else {
            echo "Falha ao atualizar o banco de dados.\n";
        }
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
