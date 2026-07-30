<?php
declare(strict_types=1);

namespace BT\App\Financeiro;

use BT\App\Financeiro\FinanceiroService;

final class FinanceiroController
{
    private FinanceiroService $service;

    public function __construct()
    {
        $this->service = new FinanceiroService();
    }

    /**
     * Processa requisições POST de salvamento
     */
    public function handlePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        if (isset($_POST['action']) && $_POST['action'] === 'salvar_contrato') {
            $this->service->salvarContrato($_POST);
            header('Location: financeiro.php?ok=contrato_salvo');
            exit;
        }
    }

    /**
     * Retorna os dados para o Dashboard Financeiro
     */
    public function getDashboardData(): array
    {
        return [
            'estatisticas' => $this->service->getEstatisticas(),
            'por_empresa' => $this->service->getFaturamentoPorEmpresa(),
            'ultimos_pagamentos' => $this->service->getUltimosPagamentos(),
            'licencas' => $this->service->listarLicencasComStatusContrato(),
            'planos' => $this->service->listarPlanos()
        ];
    }
}
