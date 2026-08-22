<?php
header('Content-Type: text/plain');
$dbHost = '127.0.0.1';
$dbName = 'bt_enterprise_master';
$dbUser = 'bt_saas_user';
$dbPass = 'BrandaoElite2026!';

try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass);

    $uuid = '5579fd18-b48e-473e-a6e1-fb3cf1335700';
    echo "Buscando UUID: $uuid\n";

    $stmt = $pdo->prepare("SELECT id, nome, produto FROM instalacoes WHERE uuid = ?");
    $stmt->execute([$uuid]);
    $inst = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inst) {
        echo "Instalação não encontrada.\n";
    } else {
        echo "Instalação ID: {$inst['id']} | Nome: {$inst['nome']}\n";

        $stmt = $pdo->prepare("SELECT * FROM dispositivos WHERE instalacao_id = ?");
        $stmt->execute([$inst['id']]);
        $dispositivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Dispositivos vinculados: " . count($dispositivos) . "\n";
        foreach ($dispositivos as $d) {
            echo "- ID: {$d['id']} | UUID: {$d['device_uuid']} | Ativo: {$d['ativo']} | Status: {$d['status']}\n";
        }

        // Busca global pelo hostname desta VM
        $hostname = gethostname();
        echo "\nBusca Global pelo Hostname: $hostname\n";
        $stmt = $pdo->prepare("SELECT * FROM dispositivos WHERE device_uuid = ?");
        $stmt->execute([$hostname]);
        $global = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($global) {
            echo "ACHOU GLOBAL: ID {$global['id']} vinculado à Instalação {$global['instalacao_id']}\n";
        } else {
            echo "Não achou nada global com esse hostname.\n";
        }
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
