<?php
declare(strict_types=1);

namespace BT\App\Licenca;

use BT\Core\Database\Database;
use BT\App\Licenca\LicencaService;
use BT\App\Services\ActivityService;
use BT\App\Auth\Auth;

final class LicencaController
{
    public function listar(): array
    {
        return Database::fetchAll(
            "SELECT
                l.*,
                i.nome AS instalacao,
                e.nome_fantasia AS empresa
             FROM licencas l
             INNER JOIN instalacoes i
                 ON i.id = l.instalacao_id
             INNER JOIN empresas e
                 ON e.id = i.empresa_id
             ORDER BY e.nome_fantasia, i.nome"
        );
    }

    public function buscar(int $id): ?array
    {
        return Database::fetch(
            "SELECT
                l.*,
                i.nome AS instalacao,
                e.nome_fantasia AS empresa
             FROM licencas l
             INNER JOIN instalacoes i
                 ON i.id = l.instalacao_id
             INNER JOIN empresas e
                 ON e.id = i.empresa_id
             WHERE l.id = ?",
            [$id]
        );
    }

    public function salvar(array $dados): bool
    {
        $service = new LicencaService();

        return $service->criar($dados);
    }

    public function atualizar(int $id, array $dados): bool
    {
        $result = Database::execute(
            "UPDATE licencas
             SET
                instalacao_id = ?,
                tipo = ?,
                status = ?,
                data_ativacao = ?,
                data_validade = ?,
                ultima_validacao = ?
             WHERE id = ?",
            [
                $dados['instalacao_id'] ?? null,
                $dados['tipo'] ?? null,
                $dados['status'] ?? null,
                $dados['data_ativacao'] ?? null,
                $dados['data_validade'] ?? null,
                $dados['ultima_validacao'] ?? null,
                $id
            ]
        );

        if ($result) {
            $user = Auth::user()['nome'] ?? 'Sistema';
            ActivityService::log('INFO', 'LICENSE', "Licença #$id atualizada para {$dados['status']}", [], $user);
        }

        return $result;
    }

    public function excluir(int $id): bool
    {
        $licenca = $this->buscar($id);
        $result = Database::execute(
            "DELETE FROM licencas
             WHERE id = ?",
            [$id]
        );

        if ($result) {
            $user = Auth::user()['nome'] ?? 'Sistema';
            $msg = "Licença excluída: " . ($licenca['empresa'] ?? "ID #$id");
            ActivityService::log('WARNING', 'LICENSE', $msg, [], $user);
        }

        return $result;
    }
}
