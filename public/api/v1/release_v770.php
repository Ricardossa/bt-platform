<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\App\Updates\PackageController;
use BT\Core\Database\Database;

header('Content-Type: text/plain');

try {
    $version = '7.7.0';
    echo "🚀 LANÇANDO VERSÃO DIAMOND v$version (SAAS SUPREME & SECURITY)...\n";

    $pc = new PackageController();

    // Gera o pacote FULL para novas instalações
    echo "📦 Gerando pacote FULL...\n";
    $pc->generateFull();

    // Gera o pacote OTA para atualizações
    echo "📦 Gerando pacote OTA...\n";
    $resOta = $pc->generate($version);

    if ($resOta['success']) {
        $finalPath = BT_ROOT . '/storage/updates/' . $resOta['file_name'];
        $checksum = hash_file('sha256', $finalPath);

        Database::execute(
            "INSERT INTO updates (produto, versao, arquivo_path, checksum_sha256, changelog, is_mandatory, canal)
             VALUES ('BT_QUEUE_ENTERPRISE', ?, ?, ?, 'Diamond v7.7.0: SaaS Supreme & Security - Identificação de Fornecedores, Cerca Eletrônica por GPS, Blindagem Anti-Word na TV e Módulo de Acertos Financeiros.', 1, 'stable')",
            [$version, $resOta['file_name'], $checksum]
        );

        echo "✅ ATUALIZAÇÃO v$version LANÇADA COM SUCESSO!\n";
        echo "Arquivo: " . $resOta['file_name'] . "\n";
        echo "SHA256: " . $checksum . "\n";
    } else {
        echo "❌ FALHA NA GERAÇÃO: " . $resOta['message'];
    }
} catch (Exception $e) { echo "❌ Erro Crítico: " . $e->getMessage(); }
?>
