<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap/app.php';

use BT\Core\Database\Database;

header('Content-Type: text/plain; charset=utf-8');

try {
    echo "🚀 Iniciando Migração da Tabela de Updates...\n";

    // 1. Adiciona a coluna produto se ela não existir
    $checkColumn = Database::fetchAll("SHOW COLUMNS FROM updates LIKE 'produto'");
    if (empty($checkColumn)) {
        Database::execute("ALTER TABLE updates ADD COLUMN produto VARCHAR(50) DEFAULT 'BT_QUEUE_ENTERPRISE' AFTER id");
        echo "✅ Coluna 'produto' adicionada.\n";
    } else {
        echo "ℹ️ Coluna 'produto' já existe.\n";
    }

    // 2. Garante que os registros antigos estão marcados como Enterprise
    Database::execute("UPDATE updates SET produto = 'BT_QUEUE_ENTERPRISE' WHERE produto IS NULL OR produto = ''");
    echo "✅ Registros antigos atualizados.\n";

    // 3. Cria índice para busca rápida de versão por produto
    try {
        Database::execute("CREATE INDEX idx_updates_produto_versao ON updates(produto, versao)");
        echo "✅ Índice de performance criado.\n";
    } catch (Exception $e) {
        echo "ℹ️ Índice já deve existir ou erro menor: " . $e->getMessage() . "\n";
    }

    echo "\n🔥 MIGRACAO CONCLUIDA COM SUCESSO!";

} catch (Exception $e) {
    echo "❌ ERRO NA MIGRACAO: " . $e->getMessage();
}
