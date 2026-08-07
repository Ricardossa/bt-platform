<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\Core\Database\Database;
header('Content-Type: application/json');
try {
    $rows = Database::fetchAll("SELECT i.*, e.nome_fantasia FROM instalacoes i JOIN empresas e ON e.id = i.empresa_id");
    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
?>
