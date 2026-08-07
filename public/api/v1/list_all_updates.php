<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\Core\Database\Database;
header('Content-Type: application/json');
try {
    $rows = Database::fetchAll("SELECT * FROM updates WHERE produto = 'BT_QUEUE_ENTERPRISE' ORDER BY id DESC LIMIT 10");
    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
?>
