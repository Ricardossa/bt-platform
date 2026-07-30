<?php
declare(strict_types=1);

namespace BT\App\Instalacao;

use BT\Core\Database\Database;
use BT\Core\Security\IdentifierGenerator;
use BT\App\Licenca\LicencaService;

final class InstallationProvisioningService
{
    public function provisionar(array $dados): array
    {
        $contexto = $this->gerarIdentificadores();

        $instalacaoId = $this->criarInstalacao(
            $dados,
            $contexto
        );

        if ($instalacaoId <= 0) {
            return [
                'success' => false,
                'message' => 'Falha ao criar instalação.'
            ];
        }

        $licencaService = new LicencaService();

        $ok = $licencaService->criar([
            'instalacao_id'    => $instalacaoId,
            'tipo'             => $dados['tipo'] ?? 'ENTERPRISE',
            'status'           => 'ATIVA',
            'data_ativacao'    => date('Y-m-d'),
            'data_validade'    => date('Y-m-d', strtotime('+1 year')),
            'ultima_validacao' => null
        ]);

        if (!$ok) {
            return [
                'success' => false,
                'message' => 'Falha ao criar licença.'
            ];
        }

        return $this->retornarContexto(
            $instalacaoId,
            $contexto
        );
    }

    private function gerarIdentificadores(): array
    {
        return [
            'uuid'  => IdentifierGenerator::uuid(),
            'token' => IdentifierGenerator::token(),
            'codigo' => strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)) // 6 caracteres
        ];
    }

    private function criarInstalacao(
        array $dados,
        array $contexto
    ): int
    {
        // Garante que o produto está definido
        $produto = $dados['produto'] ?? 'BT_QUEUE_ENTERPRISE';

        $ok = Database::execute(
            "INSERT INTO instalacoes
            (
                empresa_id,
                produto,
                nome,
                uuid,
                token,
                codigo_ativacao,
                expiracao_ativacao,
                versao,
                status
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR), ?, ?
            )",
            [
                $dados['empresa_id'],
                $produto,
                $dados['nome'],
                $contexto['uuid'],
                $contexto['token'],
                $contexto['codigo'],
                $dados['versao'] ?? '1.0.0',
                $dados['status'] ?? 'ONLINE'
            ]
        );

        if (!$ok) {
            return 0;
        }

        return (int) Database::lastInsertId();
    }

    private function retornarContexto(
        int $instalacaoId,
        array $contexto
    ): array
    {
        return [
            'success' => true,
            'instalacao_id' => $instalacaoId,
            'uuid' => $contexto['uuid'],
            'token' => $contexto['token'],
            'codigo' => $contexto['codigo']
        ];
    }
}
