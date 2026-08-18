<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\App\Updates\PackageController;
use BT\Core\Database\Database;

header('Content-Type: text/plain');

try {
    $version = '5.9.1';
    echo "🚀 GERANDO PACOTE DIAMOND v$version (MOBILE ASSETS FIX)...\n";

    $pc = new PackageController();

    // 1. Gera o FULL
    echo "🏗️ Gerando bt-enterprise.zip (FULL)...\n";
    $resFull = $pc->generateFull();
    if ($resFull['success']) {
        echo "✅ FULL Gerado. Tamanho: {$resFull['size']}\n";
    }

    // 2. Gera o OTA
    echo "🚀 Gerando Atualização v$version (OTA)...\n";
    $resOta = $pc->generate($version);

    if ($resOta['success']) {
        $finalPath = BT_ROOT . '/storage/updates/' . $resOta['file_name'];
        $checksum = hash_file('sha256', $finalPath);

        Database::execute(
            "INSERT INTO updates (produto, versao, arquivo_path, checksum_sha256, changelog, is_mandatory, canal)
             VALUES ('BT_QUEUE_ENTERPRISE', ?, ?, ?, 'Diamond v5.9.1: Correção crítica dos estilos do painel mobile.', 1, 'stable')",
            [$version, $resOta['file_name'], $checksum]
        );

        echo "✅ OTA v$version DISPONÍVEL!\n";
    }
} catch (Exception $e) { echo "Erro: " . $e->getMessage(); }
?>
