<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\Core\Database\Database;
header('Content-Type: application/json');
$rows = Database::fetchAll("SELECT * FROM instalacoes");
echo json_encode($rows, JSON_PRETTY_PRINT);
