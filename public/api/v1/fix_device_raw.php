<?php
header('Content-Type: text/plain');
$dbHost = '127.0.0.1';
$dbName = 'bt_platform';
$dbUser = 'bt_platform';
$pass = 'BTPlatform2026!';

try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $pass);

    $hostname = 'bt-platform-lab';
    $liteId = 40;

    echo "LIMPANDO VINCULOS ANTIGOS PARA $hostname...\n";

    // Primeiro removemos o dispositivo de QUALQUER instalacao (desativa)
    $stmt = $pdo->prepare("UPDATE dispositivos SET ativo = 0, status = 'BLOQUEADO' WHERE device_uuid = ?");
    $stmt->execute([$hostname]);

    // Agora forçamos o vínculo exclusivo com a Lite (ID 40) e ativamos
    $stmt = $pdo->prepare("UPDATE dispositivos SET instalacao_id = ?, ativo = 1, status = 'ONLINE' WHERE device_uuid = ?");
    $stmt->execute([$liteId, $hostname]);

    echo "Linhas afetadas: " . $stmt->rowCount() . "\n";

    if ($stmt->rowCount() > 0) {
        echo "✅ Sucesso! O dispositivo agora está FIXADO na Instalação 40.";
    } else {
        echo "⚠️ Falha ao fixar. Verifique se o hostname está correto.";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
