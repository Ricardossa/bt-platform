<?php
declare(strict_types=1);

namespace BT\App\Financeiro;

use BT\Core\Database\Database;

final class FinanceiroService
{
    /**
     * Calcula o MRR (Receita Recorrente Mensal)
     */
    public function calcularMRR(): float
    {
        $sql = "SELECT SUM(valor_mensal) as total
                FROM financeiro_contratos
                WHERE status = 'ATIVO'";

        $result = Database::fetch($sql);
        return (float) ($result['total'] ?? 0.0);
    }

    /**
     * Retorna a lista de faturamento agrupada por empresa
     */
    public function getFaturamentoPorEmpresa(): array
    {
        $sql = "SELECT
                    e.nome_fantasia as empresa,
                    COUNT(c.id) as total_licencas,
                    SUM(c.valor_mensal) as total_mensal
                FROM empresas e
                INNER JOIN instalacoes i ON i.empresa_id = e.id
                INNER JOIN licencas l ON l.instalacao_id = i.id
                INNER JOIN financeiro_contratos c ON c.licenca_id = l.id
                WHERE c.status = 'ATIVO'
                GROUP BY e.id
                ORDER BY total_mensal DESC";

        return Database::fetchAll($sql);
    }

    /**
     * Retorna os últimos pagamentos recebidos
     */
    public function getUltimosPagamentos(int $limite = 10): array
    {
        // Fix para MariaDB: Evitando parâmetro no LIMIT que causa erro 500 em algumas versões
        $sql = "SELECT
                    p.*,
                    e.nome_fantasia as empresa,
                    l.tipo as licenca_tipo
                FROM financeiro_pagamentos p
                INNER JOIN financeiro_contratos c ON p.contrato_id = c.id
                INNER JOIN licencas l ON c.licenca_id = l.id
                INNER JOIN instalacoes i ON l.instalacao_id = i.id
                INNER JOIN empresas e ON i.empresa_id = e.id
                ORDER BY p.data_pagamento DESC, p.id DESC
                LIMIT 10";

        return Database::fetchAll($sql);
    }

    /**
     * Retorna estatísticas de saúde financeira
     */
    public function getEstatisticas(): array
    {
        $mrr = $this->calcularMRR();
        $ativos = Database::fetch("SELECT COUNT(*) as total FROM financeiro_contratos WHERE status = 'ATIVO'");
        $pendentes = Database::fetch("SELECT COUNT(*) as total FROM financeiro_pagamentos WHERE status = 'PENDENTE'");

        return [
            'mrr' => (float) $mrr,
            'arr' => (float) ($mrr * 12),
            'ativos' => (int) ($ativos['total'] ?? 0),
            'pendentes' => (int) ($pendentes['total'] ?? 0),
        ];
    }

    /**
     * Retorna todas as licenças com o status do contrato (Criar ou Editar)
     */
    public function listarLicencasComStatusContrato(): array
    {
        $sql = "SELECT
                    l.id as licenca_id,
                    l.chave_licenca as licenca_chave,
                    l.tipo as licenca_tipo,
                    l.status as licenca_status,
                    i.nome as instalacao_nome,
                    e.nome_fantasia as empresa_nome,
                    c.id as contrato_id,
                    c.plano_id,
                    c.valor_mensal,
                    c.dia_vencimento,
                    c.status as contrato_status,
                    c.renovacao_automatica,
                    c.observacao,
                    p.nome as plano_nome
                FROM licencas l
                INNER JOIN instalacoes i ON l.instalacao_id = i.id
                INNER JOIN empresas e ON i.empresa_id = e.id
                LEFT JOIN financeiro_contratos c ON c.licenca_id = l.id
                LEFT JOIN financeiro_planos p ON c.plano_id = p.id
                ORDER BY e.nome_fantasia, i.nome";

        return Database::fetchAll($sql);
    }

    /**
     * Lista todos os planos disponíveis
     */
    public function listarPlanos(): array
    {
        return Database::fetchAll("SELECT * FROM financeiro_planos WHERE ativo = 1 ORDER BY valor_base ASC");
    }

    /**
     * Salva ou Atualiza um contrato financeiro
     */
    public function salvarContrato(array $dados): bool
    {
        if (!empty($dados['contrato_id'])) {
            return Database::execute(
                "UPDATE financeiro_contratos SET
                    plano_id = ?, valor_mensal = ?, dia_vencimento = ?,
                    status = ?, renovacao_automatica = ?, observacao = ?,
                    forma_pagamento = ?, data_inicio = ?
                 WHERE id = ?",
                [
                    (int) $dados['plano_id'], (float) $dados['valor_mensal'], (int) $dados['dia_vencimento'],
                    $dados['status'], (int) ($dados['renovacao_automatica'] ?? 0), $dados['observacao'],
                    $dados['forma_pagamento'] ?? 'PIX', $dados['data_inicio'] ?? date('Y-m-d'),
                    (int) $dados['contrato_id']
                ]
            );
        } else {
            return Database::execute(
                "INSERT INTO financeiro_contratos
                    (licenca_id, plano_id, valor_mensal, dia_vencimento, data_inicio, status, renovacao_automatica, observacao, forma_pagamento)
                VALUES (?, ?, ?, ?, ?, 'ATIVO', ?, ?, ?)",
                [
                    (int) $dados['licenca_id'], (int) $dados['plano_id'], (float) $dados['valor_mensal'],
                    (int) $dados['dia_vencimento'], $dados['data_inicio'] ?? date('Y-m-d'),
                    (int) ($dados['renovacao_automatica'] ?? 0), $dados['observacao'],
                    $dados['forma_pagamento'] ?? 'PIX'
                ]
            );
        }
    }
}
