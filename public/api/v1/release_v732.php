<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\App\Updates\PackageController;
use BT\Core\Database\Database;

header('Content-Type: text/plain');

try {
    $version = '7.3.2';
    echo "🚀 LANÇANDO VERSÃO DIAMOND v$version (SMART LINK BRIDGE)...\n";

    $pc = new PackageController();
    $pc->generateFull();
    $resOta = $pc->generate($version);

    if ($resOta['success']) {
        $finalPath = BT_ROOT . '/storage/updates/' . $resOta['file_name'];
        $checksum = hash_file('sha256', $finalPath);

        Database::execute(
            "INSERT INTO updates (produto, versao, arquivo_path, checksum_sha256, changelog, is_mandatory, canal)
             VALUES ('BT_QUEUE_ENTERPRISE', ?, ?, ?, 'Diamond v7.3.2: Smart Link Bridge - Resolve o problema de links não clicáveis no WhatsApp usando a Master como ponte.', 1, 'stable')",
            [$version, $resOta['file_name'], $checksum]
        );

        echo "✅ ATUALIZAÇÃO v$version LANÇADA COM SUCESSO!";
    }
} catch (Exception $e) { echo "Erro: " . $e->getMessage(); }
?>
