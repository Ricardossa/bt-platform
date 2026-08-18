<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\App\Updates\PackageController;
use BT\Core\Database\Database;

header('Content-Type: text/plain');

try {
    $version = '6.4.0';
    echo "🚀 LANÇANDO VERSÃO OFICIAL v$version (GUARDIAN SHIELD)...\n";

    $pc = new PackageController();
    $pc->generateFull();
    $resOta = $pc->generate($version);

    if ($resOta['success']) {
        $finalPath = BT_ROOT . '/storage/updates/' . $resOta['file_name'];
        $checksum = hash_file('sha256', $finalPath);

        Database::execute(
            "INSERT INTO updates (produto, versao, arquivo_path, checksum_sha256, changelog, is_mandatory, canal)
             VALUES ('BT_QUEUE_ENTERPRISE', ?, ?, ?, 'Diamond v6.4.0: Cadeado Triplo de Identidade - Bloqueio de agendamento por Nome, WhatsApp e Digital do Aparelho (Device ID).', 1, 'stable')",
            [$version, $resOta['file_name'], $checksum]
        );

        echo "✅ ATUALIZAÇÃO v$version LANÇADA COM SUCESSO!\n";
    }
} catch (Exception $e) { echo "Erro: " . $e->getMessage(); }
?>
