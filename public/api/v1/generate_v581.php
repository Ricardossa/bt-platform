<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\App\Updates\PackageController;
use BT\Core\Database\Database;

header('Content-Type: text/plain');

try {
    $version = '5.8.1';
    echo "🚀 GERANDO PACOTE OTA v$version (AUTO-SECURITY FIX)...\n";

    $pc = new PackageController();
    $res = $pc->generate($version);

    if ($res['success']) {
        $baseDir = '/opt/bt-platform/bt-platform';
        $tempPath = $baseDir . '/public/' . $res['download_url'];
        $finalFilename = $res['file_name'];
        $finalPath = $baseDir . '/storage/updates/' . $finalFilename;

        if (!is_dir($baseDir . '/storage/updates/')) {
            mkdir($baseDir . '/storage/updates/', 0775, true);
        }

        if (copy($tempPath, $finalPath)) {
            $checksum = hash_file('sha256', $finalPath);

            Database::execute(
                "INSERT INTO updates (produto, versao, arquivo_path, checksum_sha256, changelog, is_mandatory, canal)
                 VALUES ('BT_QUEUE_ENTERPRISE', ?, ?, ?, 'Hotfix v5.8.1: Ativação automática do Escudo de Segurança.', 1, 'stable')",
                [$version, $finalFilename, $checksum]
            );

            echo "✅ ATUALIZAÇÃO v$version GERADA E REGISTRADA!\n";
        }
    }
} catch (Exception $e) { echo "Erro: " . $e->getMessage(); }
?>
