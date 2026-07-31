<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap/app.php';

use BT\Core\Database\Database;

header('Content-Type: text/plain; charset=utf-8');

try {
    echo "🧪 TESTE DE OTA MULTIPRODUTO (COLETOR PRO)\n\n";

    // 1. Limpa testes anteriores de update para Coletor
    Database::execute("DELETE FROM updates WHERE produto = 'BT1_COLETOR_PRO'");

    // 2. Insere um "Falso Update" v1.7.0 para o Coletor
    Database::execute(
        "INSERT INTO updates (produto, versao, arquivo_path, checksum_sha256, changelog, is_mandatory)
         VALUES (?, ?, ?, ?, ?, ?)",
        ['BT1_COLETOR_PRO', '1.7.0', 'bt1_v1_7_test.apk', 'fakehash', 'Melhorias de bateria e hardware', 1]
    );
    echo "✅ Simulação de Update v1.7.0 inserida no banco.\n";

    // 3. Simula um pedido de sincronismo de um celular na v1.4.0
    $payload = [
        'uuid' => 'af945396-7f5a-4b4d-b65e-511505142dc5',
        'token' => '1043DBB812935D3CA10C7ADEC28B5FEA4CB04B22A9522E0CDEE0744C0A4B3F4E',
        'produto' => 'BT1_COLETOR_PRO',
        'versao' => '1.4.0',
        'device_uuid' => 'DMS8Z8RD9-78NGQ5'
    ];

    echo "\nEnviando Simulação de Sync (App v1.4.0)...\n";

    $ch = curl_init('http://localhost:8080/api/v1/sync.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($res, true);
    echo "Resposta do Servidor:\n";
    print_r($json['sync']['update'] ?? 'Erro no retorno');

    if (isset($json['sync']['update']['available']) && $json['sync']['update']['available'] === true) {
        echo "\n🔥 SUCESSO: A Master detectou a versão antiga e ofereceu o APK v1.7.0!";
    } else {
        echo "\n❌ FALHA: O servidor não ofereceu a atualização.";
    }

    // Limpeza final
    Database::execute("DELETE FROM updates WHERE arquivo_path = 'bt1_v1_7_test.apk'");

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
