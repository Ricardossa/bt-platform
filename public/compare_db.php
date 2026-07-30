<?php
header('Content-Type: text/plain');

function checkDB($ip, $label) {
    echo "--- $label ($ip) ---\n";
    try {
        $pdo = new PDO("mysql:host=$ip;dbname=bt_platform", "bt_platform", "BTPlatform2026!");
        $res = $pdo->query("SELECT id, nome, uuid, produto, token FROM instalacoes WHERE produto = 'BT1_COLETOR_PRO'")->fetchAll(PDO::FETCH_ASSOC);
        print_r($res);
        $pdo = null;
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage() . "\n";
    }
}

checkDB("192.168.100.244", "VM Z (ORIGINAL)");
checkDB("192.168.100.245", "VM Y (NOVA)");
