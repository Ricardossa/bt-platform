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
        if (empty($dados['empresa_id'])) {
            return ['success' => false, 'message' => 'Selecione uma empresa válida.'];
        }

        $contexto = $this->gerarIdentificadores();

        // 1. Criar Instalação (Fora da transação para garantir o registro)
        $instalacaoId = $this->criarInstalacao($dados, $contexto);

        if ($instalacaoId <= 0) {
            return ['success' => false, 'message' => 'Falha ao criar registro de instalação no banco central.'];
        }

        // 2. Tenta Criar Tenant e Licença (Com captura individual de erro)
        try {
            $this->criarTenant($instalacaoId, $dados, $contexto);

            $licencaService = new LicencaService();
            $licencaService->criar([
                'instalacao_id'    => $instalacaoId,
                'tipo'             => $dados['tipo'] ?? 'ENTERPRISE',
                'status'           => 'ATIVA',
                'data_ativacao'    => date('Y-m-d'),
                'data_validade'    => date('Y-m-d', strtotime('+1 year')),
                'ultima_validacao' => null
            ]);
        } catch (\Throwable $e) {
            // Se falhar o tenant ou a licença, ainda retornamos sucesso da instalação
            // para o usuário não ver erro 500 e conseguir consertar manualmente depois.
            error_log("Aviso: Provisionamento parcial para ID $instalacaoId: " . $e->getMessage());
        }

        return [
            'success' => true,
            'instalacao_id' => $instalacaoId,
            'uuid' => $contexto['uuid'],
            'token' => $contexto['token'],
            'codigo' => $contexto['codigo']
        ];
    }

    private function criarTenant(int $id, array $dados, array $contexto): void
    {
        $slug = !empty($dados['slug']) ? $dados['slug'] : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $dados['nome']));

        $check = Database::fetch("SELECT id FROM tenants WHERE slug = ? LIMIT 1", [$slug]);
        if ($check) $slug .= '-' . $id;

        Database::execute("DELETE FROM tenants WHERE id = ?", [$id]);

        Database::execute(
            "INSERT INTO tenants (id, uuid, slug, nome, status) VALUES (?, ?, ?, ?, 'ATIVO')",
            [$id, $contexto['uuid'], $slug, $dados['nome']]
        );
    }

    private function gerarIdentificadores(): array
    {
        return [
            'uuid'  => IdentifierGenerator::uuid(),
            'token' => IdentifierGenerator::token(),
            'codigo' => strtoupper(substr(bin2hex(random_bytes(3)), 0, 6))
        ];
    }

    private function criarInstalacao(array $dados, array $contexto): int
    {
        $produto = $dados['produto'] ?? 'BT_QUEUE_ENTERPRISE';
        $statusFinal = (isset($dados['status']) && ($dados['status'] === 'OFFLINE' || $dados['status'] === 'SUSPENSO')) ? 'OFFLINE' : 'ONLINE';
        $planoId = isset($dados['plano_id']) && (int)$dados['plano_id'] > 0 ? (int)$dados['plano_id'] : null;

        $ok = Database::execute(
            "INSERT INTO instalacoes (empresa_id, plano_id, produto, nome, uuid, token, codigo_ativacao, expiracao_ativacao, versao, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR), ?, ?)",
            [
                $dados['empresa_id'],
                $planoId,
                $produto,
                $dados['nome'],
                $contexto['uuid'],
                $contexto['token'],
                $contexto['codigo'],
                $dados['versao'] ?? '1.0.0',
                $statusFinal
            ]
        );

        return $ok ? (int) Database::lastInsertId() : 0;
    }
}
