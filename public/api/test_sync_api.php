<?php
require_once __DIR__ . '/../../bootstrap/app.php';
use BT\Core\Database\Database;

header('Content-Type: application/json');

try {
    // Busca dados reais do Coletor Pro para simular
    $instalacao = Database::fetch("SELECT uuid, token FROM instalacoes WHERE id = 14");
    if (!$instalacao) die("Erro: Instalacao 14 nao encontrada.");

    $payload = [
        'uuid' => $instalacao['uuid'],
        'token' => $instalacao['token'],
        'produto' => 'BT1_COLETOR_PRO',
        'versao' => '1.0.0',
        'device_uuid' => 'DMS7I41SK-H0GZO6'
    ];

    echo "Payload Simulado:\n";
    print_r($payload);

    echo "\n\nResultado da API:\n";
    $ch = curl_init('http://localhost:8080/api/v1/sync.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);

    echo $res;

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
