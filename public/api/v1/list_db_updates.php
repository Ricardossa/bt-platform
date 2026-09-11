<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\Core\Database\Database;

header('Content-Type: application/json');

try {
    $res = Database::fetchAll("SELECT id, produto, versao, arquivo_path, checksum_sha256 FROM updates ORDER BY id DESC LIMIT 20");
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
