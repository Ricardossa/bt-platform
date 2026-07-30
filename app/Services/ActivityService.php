<?php

declare(strict_types=1);

namespace BT\App\Services;

use BT\Core\Database\Database;
use Exception;

final class ActivityService
{
    /**
     * Registra uma atividade no banco de dados.
     */
    public static function log(
        string $tipo,
        string $categoria,
        string $mensagem,
        array $metadata = [],
        ?string $usuario = null
    ): void {
        try {
            self::ensureTableExists();

            Database::execute(
                "INSERT INTO atividades (tipo, categoria, mensagem, metadata, usuario) VALUES (?, ?, ?, ?, ?)",
                [
                    $tipo,
                    $categoria,
                    $mensagem,
                    !empty($metadata) ? json_encode($metadata) : null,
                    $usuario
                ]
            );
        } catch (Exception $e) {
            // Falha silenciosa para não quebrar a aplicação principal
            error_log("Erro ao registrar atividade: " . $e->getMessage());

            // Tenta criar a tabela e reinserir uma vez se falhar (pode ser tabela inexistente)
            try {
                 Database::execute("CREATE TABLE IF NOT EXISTS atividades (id INT AUTO_INCREMENT PRIMARY KEY, tipo VARCHAR(50), categoria VARCHAR(50), mensagem TEXT, usuario VARCHAR(100), metadata TEXT, data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } catch (Exception $e2) {}
        }
    }

    private static function ensureTableExists(): void
    {
        static $checked = false;
        if ($checked) return;

        // Versão simplificada sem restrições de ENUM para máxima compatibilidade
        $sql = "CREATE TABLE IF NOT EXISTS atividades (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tipo VARCHAR(50) DEFAULT 'INFO',
            categoria VARCHAR(50) DEFAULT 'SYSTEM',
            mensagem TEXT,
            usuario VARCHAR(100),
            metadata TEXT,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        try {
            Database::execute($sql);
        } catch (Exception $e) {}

        $checked = true;
    }

    public static function getRecent(int $limit = 10): array
    {
        try {
            // Alguns drivers de banco têm problemas com parâmetros no LIMIT
            // Como o $limit é um inteiro controlado pelo nosso código, concatenamos diretamente.
            return Database::fetchAll(
                "SELECT * FROM atividades ORDER BY id DESC LIMIT " . (int) $limit
            );
        } catch (Exception $e) {
            return [];
        }
    }

    public static function countTodaySyncs(): int
    {
        try {
            $result = Database::fetch(
                "SELECT COUNT(*) as total FROM atividades
                 WHERE categoria = 'SYNC'
                 AND data_criacao >= CURDATE()"
            );
            return (int) ($result['total'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
}
