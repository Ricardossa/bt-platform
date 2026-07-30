<?php

declare(strict_types=1);

namespace BT\App\Empresa;

use BT\Core\Database\Database;
use BT\App\Services\ActivityService;
use BT\App\Auth\Auth;
use Exception;

final class EmpresaController
{
    /**
     * Garante que as novas colunas existam no banco de dados.
     */
    private function ensureColumnsExist(): void
    {
        static $checked = false;
        if ($checked) return;

        $columns = [
            'telefone' => "VARCHAR(20) NULL",
            'whatsapp' => "VARCHAR(20) NULL",
            'email' => "VARCHAR(100) NULL",
            'site' => "VARCHAR(255) NULL",
            'endereco' => "VARCHAR(255) NULL",
            'cidade' => "VARCHAR(100) NULL",
            'estado' => "CHAR(2) NULL",
            'cep' => "VARCHAR(10) NULL"
        ];

        foreach ($columns as $column => $definition) {
            try {
                // Tenta adicionar a coluna. Se já existir, o Database::execute retornará erro e o catch tratará.
                Database::execute("ALTER TABLE empresas ADD COLUMN $column $definition");
            } catch (Exception $e) {
                // Silencioso: Coluna provavelmente já existe
            }
        }

        $checked = true;
    }

    public function salvar(array $dados): bool
    {
        $this->ensureColumnsExist();

        $result = Database::execute(
            "INSERT INTO empresas 
            (
                nome_fantasia, razao_social, cnpj, tema,
                telefone, whatsapp, email, site,
                endereco, cidade, estado, cep
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $dados['nome_fantasia'] ?? '',
                $dados['razao_social']  ?? '',
                $dados['cnpj']          ?? '',
                $dados['tema']          ?? 'blue',
                $dados['telefone']      ?? null,
                $dados['whatsapp']      ?? null,
                $dados['email']         ?? null,
                $dados['site']          ?? null,
                $dados['endereco']      ?? null,
                $dados['cidade']        ?? null,
                $dados['estado']        ?? null,
                $dados['cep']           ?? null
            ]
        );

        if ($result) {
            $user = Auth::user()['nome'] ?? 'Sistema';
            ActivityService::log('SUCCESS', 'COMPANY', "Empresa cadastrada: {$dados['nome_fantasia']}", [], $user);
        }

        return $result;
    }

    public function atualizar(int $id, array $dados): bool
    {
        $this->ensureColumnsExist();

        return Database::execute(
            "UPDATE empresas 
            SET
                nome_fantasia = ?,
                razao_social = ?,
                cnpj = ?,
                tema = ?,
                telefone = ?,
                whatsapp = ?,
                email = ?,
                site = ?,
                endereco = ?,
                cidade = ?,
                estado = ?,
                cep = ?
            WHERE id = ?",
            [
                $dados['nome_fantasia'] ?? '',
                $dados['razao_social']  ?? '',
                $dados['cnpj']          ?? '',
                $dados['tema']          ?? 'blue',
                $dados['telefone']      ?? null,
                $dados['whatsapp']      ?? null,
                $dados['email']         ?? null,
                $dados['site']          ?? null,
                $dados['endereco']      ?? null,
                $dados['cidade']        ?? null,
                $dados['estado']        ?? null,
                $dados['cep']           ?? null,
                $id
            ]
        );
    }

    public function buscarPorId(int $id): ?array
    {
        $this->ensureColumnsExist();
        return Database::fetch(
            "SELECT * FROM empresas WHERE id = ?",
            [$id]
        );
    }

    public function listarTodas(): array
    {
        $this->ensureColumnsExist();
        return Database::fetchAll(
            "SELECT * FROM empresas ORDER BY nome_fantasia"
        );
    }

    // ==========================================
    // MÉTODOS PARA A V2
    // ==========================================
    
    public function buscarServicosContratados(int $empresaId): array
    {
        return Database::fetchAll("
            SELECT 
                sc.*,
                s.nome AS servico_nome,
                s.codigo AS servico_codigo,
                s.icone,
                (SELECT COUNT(*) FROM dispositivos d 
                 WHERE d.instalacao_id IN (SELECT id FROM instalacoes WHERE empresa_id = ?) 
                 AND d.servico = s.codigo) AS total_dispositivos
            FROM servicos_contratados sc
            JOIN servicos s ON s.id = sc.servico_id
            WHERE sc.empresa_id = ? AND sc.status = 'ATIVO'
            ORDER BY s.ordem
        ", [$empresaId, $empresaId]);
    }

    public function listarInstalacoes(int $empresaId): array
    {
        return Database::fetchAll("
            SELECT i.*, e.nome_fantasia AS empresa
            FROM instalacoes i
            INNER JOIN empresas e ON e.id = i.empresa_id
            WHERE i.empresa_id = ?
            ORDER BY i.nome
        ", [$empresaId]);
    }

    public function listarLicencas(int $empresaId): array
    {
        return Database::fetchAll("
            SELECT l.*, i.nome AS instalacao
            FROM licencas l
            INNER JOIN instalacoes i ON i.id = l.instalacao_id
            WHERE i.empresa_id = ?
            ORDER BY l.id DESC
        ", [$empresaId]);
    }

    public function contarInstalacoesPorStatus(int $empresaId): array
    {
        $result = Database::fetchAll("
            SELECT status, COUNT(*) as total
            FROM instalacoes
            WHERE empresa_id = ?
            GROUP BY status
        ", [$empresaId]);
        
        $counts = [];
        foreach ($result as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }

    public function contarLicencasPorStatus(int $empresaId): array
    {
        $result = Database::fetchAll("
            SELECT l.status, COUNT(*) as total
            FROM licencas l
            INNER JOIN instalacoes i ON i.id = l.instalacao_id
            WHERE i.empresa_id = ?
            GROUP BY l.status
        ", [$empresaId]);
        
        $counts = [];
        foreach ($result as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }
}
