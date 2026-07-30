<?php
require_once __DIR__ . '/../../bootstrap/app.php';
use BT\Core\Database\Database;
header('Content-Type: text/plain');
try {
    $res = Database::fetchAll("DESCRIBE atividades");
    print_r($res);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
