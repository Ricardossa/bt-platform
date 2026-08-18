<?php

declare(strict_types=1);

namespace BT\App\Services;

use BT\Core\Database\Database;
use Exception;

/**
 * Assistente de Inteligência Artificial para o Centro de Operações Master.
 * Realiza auditorias de saúde do império e consulta o cérebro Xeon.
 */
final class MasterAIService
{
    private string $ollamaUrl = 'http://192.168.100.250:11434/api/generate';

    /**
     * Gera um briefing estratégico sobre a saúde dos clientes e finanças.
     */
    public function getHealthBriefing(): string
    {
        try {
            $data = $this->collectVitalSigns();

            $prompt = "Você é o Comandante do NOC (Centro de Operações) da Brandão Tech. Analise os seguintes dados do império de software e dê um resumo estratégico, destacando riscos e ações necessárias:\n\n" .
                      "1. DISPOSITIVOS OFFLINE: {$data['offline_count']} (mais de 2h sem sinal)\n" .
                      "2. LICENÇAS VENCENDO EM 7 DIAS: {$data['expiring_soon']}\n" .
                      "3. LICENÇAS JÁ VENCIDAS E ATIVAS: {$data['expired_active']}\n" .
                      "4. FATURAMENTO PENDENTE: R$ " . number_format($data['pending_revenue'], 2, ',', '.') . " ({$data['pending_invoices']} boletos em aberto)\n\n" .
                      "Seja direto, autoritário como um comandante e use emojis.";

            return $this->queryXeon($prompt);

        } catch (\Throwable $e) {
            return "⚠️ Falha ao conectar com o cérebro Xeon: " . $e->getMessage();
        }
    }

    private function collectVitalSigns(): array
    {
        // 1. Dispositivos Offline (> 2h)
        $offline = Database::fetch("SELECT COUNT(*) as total FROM dispositivos WHERE ultima_sincronizacao < DATE_SUB(NOW(), INTERVAL 2 HOUR) AND ativo = 1");

        // 2. Licenças Vencendo (7 dias)
        $vencendo = Database::fetch("SELECT COUNT(*) as total FROM licencas WHERE data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status = 'ATIVA'");

        // 3. Licenças Vencidas mas Ativas
        $vencidas = Database::fetch("SELECT COUNT(*) as total FROM licencas WHERE data_validade < CURDATE() AND status = 'ATIVA'");

        // 4. Financeiro Pendente (v6.8.1 Fix: Column name)
        $financeiro = Database::fetch("SELECT COUNT(*) as total, SUM(valor_cobrado) as valor FROM financeiro_pagamentos WHERE status = 'PENDENTE'");

        return [
            'offline_count' => (int)($offline['total'] ?? 0),
            'expiring_soon' => (int)($vencendo['total'] ?? 0),
            'expired_active' => (int)($vencidas['total'] ?? 0),
            'pending_invoices' => (int)($financeiro['total'] ?? 0),
            'pending_revenue' => (float)($financeiro['valor'] ?? 0.0)
        ];
    }

    private function queryXeon(string $prompt): string
    {
        $payload = json_encode([
            'model' => 'llama3.2:latest',
            'prompt' => $prompt,
            'stream' => false
        ]);

        $ch = curl_init($this->ollamaUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("IA fora de área (HTTP $httpCode)");
        }

        $json = json_decode($response, true);
        return $json['response'] ?? "O cérebro não conseguiu processar o briefing.";
    }
}
