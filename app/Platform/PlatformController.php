<?php

declare(strict_types=1);

namespace BT\App\Platform;

use BT\Core\Database\Database;
use Exception;

final class PlatformController
{
    /**
     * Garante que as colunas existam no banco de dados.
     */
    private function ensureColumnsExist(): void
    {
        static $checked = false;
        if ($checked) return;

        $columns = [
            'whatsapp'       => "VARCHAR(20) NULL",
            'instagram'      => "VARCHAR(100) NULL",
            'versao_sistema' => "VARCHAR(20) NULL",
            'telefone'       => "VARCHAR(20) NULL",
            'email'          => "VARCHAR(100) NULL"
        ];

        foreach ($columns as $column => $definition) {
            try {
                Database::execute("ALTER TABLE platform ADD COLUMN $column $definition");
            } catch (Exception $e) {
                // Silencioso
            }
        }

        $checked = true;
    }

    public function buscar(): ?array
    {
        $this->ensureColumnsExist();

        $row = Database::fetch("SELECT * FROM platform ORDER BY id ASC LIMIT 1");

        if (!$row) {
            Database::execute("INSERT INTO platform (nome_empresa, versao_sistema) VALUES ('BT Queue Platform', 'v2.0.0')");
            $row = Database::fetch("SELECT * FROM platform ORDER BY id ASC LIMIT 1");
        }

        return $row;
    }

    public function salvar(array $dados): bool
    {
        $this->ensureColumnsExist();

        $current = $this->buscar();
        $id = $current['id'] ?? null;

        if (!$id) return false;

        return Database::execute(
            "UPDATE platform
             SET
                nome_empresa = ?,
                razao_social = ?,
                cnpj = ?,
                telefone = ?,
                email = ?,
                site = ?,
                tema = ?,
                logo = ?,
                whatsapp = ?,
                instagram = ?,
                versao_sistema = ?
             WHERE id = ?",
            [
                $dados['nome_empresa'] ?? '',
                $dados['razao_social'] ?? '',
                $dados['cnpj']         ?? '',
                $dados['telefone']     ?? null,
                $dados['email']        ?? null,
                $dados['site']         ?? null,
                $dados['tema']         ?? 'blue',
                $dados['logo']         ?? null,
                $dados['whatsapp']     ?? null,
                $dados['instagram']    ?? null,
                $dados['versao_sistema'] ?? null,
                $id
            ]
        );
    }
}
