<?php
require_once __DIR__ . '/../bootstrap/app.php';
use BT\Core\Database\Database;

try {
    echo "--- DATABASE CHECK ---\n";
    $tables = Database::fetchAll("SHOW TABLES");
    foreach($tables as $row) {
        echo current($row) . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
