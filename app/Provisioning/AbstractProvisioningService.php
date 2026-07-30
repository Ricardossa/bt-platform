<?php
declare(strict_types=1);

namespace BT\App\Provisioning;

use BT\Core\Database\Database;
use BT\Core\Security\IdentifierGenerator;
use BT\App\Licenca\LicencaService;

abstract class AbstractProvisioningService
{
    abstract protected function getProduto(): string;
    abstract protected function getTipoLicenca(): string;

    public function criarInstalacao(array $dados): array
    {
        $contexto = $this->gerarIdentificadores();

        $instalacaoId = $this->gravarInstalacao($dados, $contexto);

        if ($instalacaoId <= 0) {
            return ['success' => false, 'message' => 'Falha ao criar instalação.'];
        }

        $this->criarLicenca($instalacaoId, $dados);

        return [
            'success' => true,
            'instalacao_id' => $instalacaoId,
            'uuid' => $contexto['uuid'],
            'token' => $contexto['token']
        ];
    }

    private function gerarIdentificadores(): array
    {
        return [
            'uuid'  => IdentifierGenerator::uuid(),
            'token' => IdentifierGenerator::token()
        ];
    }

    private function gravarInstalacao(array $dados, array $contexto): int
    {
        $ok = Database::execute(
            "INSERT INTO instalacoes
            (empresa_id, produto, nome, uuid, token, versao, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $dados['empresa_id'],
                $this->getProduto(),
                $dados['nome'],
                $contexto['uuid'],
                $contexto['token'],
                $dados['versao'] ?? '1.0.0',
                $dados['status'] ?? 'ONLINE'
            ]
        );

        if (!$ok) {
            return 0;
        }

        // Verifica se o método lastInsertId existe
        if (method_exists(Database::class, 'lastInsertId')) {
            return (int) Database::lastInsertId();
        }

        // Fallback: buscar o último ID inserido
        $result = Database::fetch("SELECT LAST_INSERT_ID() as id");
        return $result ? (int) $result['id'] : 0;
    }

    private function criarLicenca(int $instalacaoId, array $dados): void
    {
        $licencaService = new LicencaService();
        $licencaService->criar([
            'instalacao_id'    => $instalacaoId,
            'tipo'             => $dados['tipo'] ?? $this->getTipoLicenca(),
            'status'           => 'ATIVA',
            'data_ativacao'    => date('Y-m-d'),
            'data_validade'    => date('Y-m-d', strtotime('+1 year')),
            'ultima_validacao' => null
        ]);
    }
}
