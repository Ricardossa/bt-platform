<?php
try {
    $config = require __DIR__ . '/../../../config/config.php';
    $dbCfg = $config['database'];

    $dsn = "mysql:host={$dbCfg['host']};dbname={$dbCfg['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbCfg['username'], $dbCfg['password']);

    $uuid = '5579fd18-b48e-473e-a6e1-fb3cf1335700';
    $stmt = $pdo->prepare("SELECT id, nome, produto FROM instalacoes WHERE uuid = ?");
    $stmt->execute([$uuid]);
    $instalacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$instalacao) die("Instalacao nao encontrada");

    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM dispositivos WHERE instalacao_id = ? AND ativo = 1");
    $stmt->execute([$instalacao['id']]);
    $ativos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $pdo->prepare("SELECT * FROM dispositivos WHERE instalacao_id = ?");
    $stmt->execute([$instalacao['id']]);
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'instalacao' => $instalacao,
        'total_ativos' => $ativos,
        'dispositivos' => $todos
    ]);

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
