<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\Core\Database\Database;

header('Content-Type: text/plain');

$uuid = '3b2a610e-02c3-47f1-b16f-e095b25f5b16';
$hostname = 'DESKTOP-9L4TBKT';

try {
    echo "--- AUDITORIA DE INSTALAÇÃO MASTER ---\n";

    $inst = Database::fetch("SELECT id, nome, uuid FROM instalacoes WHERE uuid = ?", [$uuid]);
    if (!$inst) {
        die("Instalação com UUID $uuid não encontrada na Master.\n");
    }
    echo "Instalação encontrada: {$inst['nome']} (ID: {$inst['id']})\n\n";

    echo "--- DISPOSITIVOS VINCULADOS A ESTA INSTALAÇÃO ---\n";
    $devs = Database::fetchAll("SELECT id, device_uuid, fabricante, modelo, ultima_sincronizacao FROM dispositivos WHERE instalacao_id = ?", [$inst['id']]);
    print_r($devs);

    echo "\n--- BUSCA GLOBAL PELO HOSTNAME ($hostname) ---\n";
    $global = Database::fetch("SELECT d.id, d.device_uuid, d.instalacao_id, i.nome as instalacao_nome
                               FROM dispositivos d
                               LEFT JOIN instalacoes i ON i.id = d.instalacao_id
                               WHERE d.device_uuid = ?", [$hostname]);
    print_r($global);

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
