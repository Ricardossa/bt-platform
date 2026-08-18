<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

$pageTitle = 'Tabela de Preços e Planos';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .plan-container { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 30px; }
    .plan-card { background: var(--card); border: 1px solid var(--border); border-radius: 15px; padding: 30px; position: relative; }
    .plan-card h3 { font-size: 20px; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .plan-card p { font-size: 14px; color: var(--text2); margin-bottom: 15px; line-height: 1.6; }
    .plan-card ul { list-style: none; margin-bottom: 25px; }
    .plan-card li { font-size: 13px; color: var(--text2); margin-bottom: 8px; display: flex; align-items: flex-start; gap: 10px; }
    .plan-card li i { color: var(--secondary); margin-top: 4px; }

    .price-badge { background: var(--sidebar); border: 1px solid var(--border); border-radius: 12px; padding: 20px; text-align: center; }
    .price-badge .main-val { font-size: 28px; font-weight: 900; color: #fff; }
    .price-badge .sub-val { font-size: 14px; color: var(--text3); }

    .summary-table { width: 100%; border-collapse: collapse; margin-top: 30px; background: var(--card); border-radius: 12px; overflow: hidden; border: 1px solid var(--border); }
    .summary-table th { background: var(--sidebar); text-align: left; padding: 15px; font-size: 12px; color: var(--text3); text-transform: uppercase; }
    .summary-table td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 14px; }
    .summary-table tr:last-child td { border-bottom: none; }
</style>

<main class="bt-main">

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="margin:0;">🚀 Modelos de Implantação — BT Queue</h2>
            <p style="color:var(--text2); font-size:14px;">Referência comercial para novos contratos e upgrades.</p>
        </div>
    </div>

    <div class="plan-container">
        <!-- PLANO LOCAL -->
        <div class="plan-card">
            <h3>🖥️ Plano Local</h3>
            <p>O servidor do BT Queue fica instalado na própria empresa, utilizando a infraestrutura física do cliente.</p>

            <div style="font-size:11px; font-weight:bold; color:var(--text3); margin-bottom:10px; text-transform:uppercase;">Requisitos do Cliente:</div>
            <ul>
                <li><i class="fa-solid fa-check"></i> IP público estático (essencial para mobile)</li>
                <li><i class="fa-solid fa-check"></i> Máquina ou servidor local disponível</li>
                <li><i class="fa-solid fa-check"></i> Infraestrutura de rede adequada</li>
                <li><i class="fa-solid fa-check"></i> Conexão de internet estável</li>
            </ul>

            <div class="price-badge">
                <div class="main-val">R$ 200,00<small style="font-size:14px; font-weight:400;">/mês</small></div>
                <div class="sub-val">1º mês: R$ 260,00 (Implantação)</div>
            </div>
        </div>

        <!-- PLANO CLOUD -->
        <div class="plan-card" style="border-color: var(--secondary);">
            <div style="position:absolute; top:20px; right:20px; font-size:10px; background:var(--secondary); color:#fff; padding:3px 10px; border-radius:10px; font-weight:bold;">RECOMENDADO</div>
            <h3>☁️ Plano Cloud</h3>
            <p>Hospedado em uma VPS na nuvem Brandão Tech. Permite acesso global e elimina a necessidade de hardware local.</p>

            <div style="font-size:11px; font-weight:bold; color:var(--text3); margin-bottom:10px; text-transform:uppercase;">Vantagens Enterprise:</div>
            <ul>
                <li><i class="fa-solid fa-check"></i> Sem necessidade de IP Público Estático</li>
                <li><i class="fa-solid fa-check"></i> Backup Diário Automatizado na Master</li>
                <li><i class="fa-solid fa-check"></i> Acesso remoto de qualquer lugar (NOC)</li>
                <li><i class="fa-solid fa-check"></i> Manutenção 100% gerenciada por nós</li>
            </ul>

            <div class="price-badge">
                <div class="main-val">R$ 260,00<small style="font-size:14px; font-weight:400;">/mês</small></div>
                <div class="sub-val">1º mês: R$ 320,00 (Configuração + VPS)</div>
            </div>
        </div>
    </div>

    <h3 style="margin:40px 0 15px; font-size:18px;">📋 Resumo Comparativo</h3>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Plano</th>
                <th>Infraestrutura</th>
                <th>1º Mês</th>
                <th>Mensalidade</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><b>Local</b></td>
                <td>Servidor na empresa + IP estático + internet</td>
                <td style="font-weight:bold; color:var(--secondary);">R$ 260</td>
                <td>R$ 200/mês</td>
            </tr>
            <tr>
                <td><b>Cloud</b></td>
                <td>VPS na nuvem + internet do cliente</td>
                <td style="font-weight:bold; color:var(--secondary);">R$ 320</td>
                <td>R$ 260/mês</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:30px; background:rgba(245, 165, 36, 0.05); border:1px solid rgba(245, 165, 36, 0.1); padding:20px; border-radius:12px; font-size:13px; color:var(--text2); display:flex; gap:15px; align-items:center;">
        <i class="fa-solid fa-circle-exclamation" style="color:#f5a524; font-size:20px;"></i>
        <span><b>Nota Importante:</b> Ambos os modelos dependem de uma conexão de internet adequada para o funcionamento do BT Queue e de seus recursos que necessitam de comunicação externa.</span>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
