<?php
header('Content-Type: text/plain');
$dbHost = '127.0.0.1';
$dbName = 'bt_platform';
$dbUser = 'bt_platform';
$pass = 'BTPlatform2026!';

try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $pass);

    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo implode("\n", $tables);

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
