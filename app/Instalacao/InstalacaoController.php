<?php
declare(strict_types=1);

namespace BT\App\Instalacao;

use BT\Core\Database\Database;
use BT\Core\Security\IdentifierGenerator;
use BT\App\Instalacao\InstallationProvisioningService;

final class InstalacaoController
{
    public function listar(): array
    {
        return Database::fetchAll(
            "SELECT
                i.*,
                e.nome_fantasia AS empresa
             FROM instalacoes i
             LEFT JOIN empresas e
                 ON e.id = i.empresa_id
             ORDER BY e.nome_fantasia, i.nome"
        );
    }

    public function buscar(int $id): ?array
    {
        return Database::fetch(
            "SELECT *
             FROM instalacoes
             WHERE id = ?",
            [$id]
        );
    }

    public function salvar(array $dados): bool
{
    $service = new InstallationProvisioningService();
    
    // Adiciona o produto aos dados
    $dados['produto'] = $dados['produto'] ?? 'BT_QUEUE_ENTERPRISE';
    
    $resultado = $service->provisionar($dados);
    return $resultado['success'];
}

    public function atualizar(int $id, array $dados): bool
    {
        return Database::execute(
            "UPDATE instalacoes
             SET
                empresa_id = ?,
                plano_id = ?,
                nome = ?,
                uuid = ?,
                versao = ?,
                status = ?
             WHERE id = ?",
            [
                $dados['empresa_id'],
                $dados['plano_id'] > 0 ? $dados['plano_id'] : null,
                $dados['nome'],
                $dados['uuid'],
                $dados['versao'],
                $dados['status'],
                $id
            ]
        );
    }

    

    public function listarComDispositivos(): array
    {
        return Database::fetchAll(
            "SELECT
                i.*,
                e.nome_fantasia AS empresa,
                p.nome AS plano_nome,
                (SELECT COUNT(*) FROM dispositivos d WHERE d.instalacao_id = i.id AND d.ativo = 1) AS total_dispositivos,
                (SELECT MAX(ultima_sincronizacao) FROM dispositivos d WHERE d.instalacao_id = i.id AND d.ativo = 1) AS ultimo_dispositivo
             FROM instalacoes i
             LEFT JOIN empresas e ON e.id = i.empresa_id
             LEFT JOIN planos p ON p.id = i.plano_id
             ORDER BY e.nome_fantasia, i.nome"
        );
    }

    public function gerarCodigoAtivacao(int $id): string
    {
        $codigo = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        Database::execute(
            "UPDATE instalacoes SET codigo_ativacao = ?, expiracao_ativacao = DATE_ADD(NOW(), INTERVAL 2 HOUR) WHERE id = ?",
            [$codigo, $id]
        );
        return $codigo;
    }

    public function buscarDispositivos(int $instalacaoId): array
    {
        return Database::fetchAll(
            "SELECT *
             FROM dispositivos
             WHERE instalacao_id = ?
             AND ativo = 1
             ORDER BY ultima_sincronizacao DESC",
            [$instalacaoId]
        );
    }

    /**
     * Remove um dispositivo vinculado à instalação.
     */
    public function removerDispositivo(int $id): bool
    {
        $service = new \BT\App\Services\DeviceService();
        return $service->remover($id);
    }

    public function excluir(int $id): bool
    {
        return Database::execute(
            "DELETE FROM instalacoes
             WHERE id = ?",
            [$id]
        );
    }
}
