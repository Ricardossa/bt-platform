<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\App\Auth\Auth;
use BT\App\Financeiro\FinanceiroController;

if (!Auth::check()) {
    header('Location: /login.php');
    exit;
}

$controller = new FinanceiroController();
$controller->handlePost();

$data = $controller->getDashboardData();
$stats = $data['estatisticas'];

$pageTitle = 'Gestão Financeira Master';

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .finance-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 25px; margin-top: 30px; }
    .finance-card { background: var(--card); border-radius: 12px; padding: 25px; border: 1px solid var(--border); box-shadow: var(--shadow); }
    .stat-box { text-align: center; padding: 20px; border-radius: 10px; background: var(--sidebar); border: 1px solid var(--border); }
    .stat-val { font-size: 28px; font-weight: bold; color: var(--secondary); margin: 10px 0; }
    .stat-label { font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 1px; }
    .money { color: var(--success) !important; }

    .bt-modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); }
    .modal-content { background: var(--card); margin: 5% auto; padding: 30px; border-radius: 15px; width: 550px; border: 1px solid var(--border); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
</style>

<main class="bt-main">

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="margin:0;">💰 Dashboard Financeiro</h2>
            <p style="color:var(--text2); font-size:14px;">Visão consolidada de faturamento e assinaturas.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <button class="bt-button" onclick="location.reload()"><i class="fa-solid fa-sync"></i> Atualizar</button>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:20px; margin-top:30px;">
        <div class="stat-box">
            <div class="stat-label">MRR (Recorrência Mensal)</div>
            <div class="stat-val money">R$ <?= number_format((float) $stats['mrr'], 2, ',', '.') ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">ARR (Projeção Anual)</div>
            <div class="stat-val">R$ <?= number_format((float) $stats['arr'], 2, ',', '.') ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Contratos Ativos</div>
            <div class="stat-val" style="color:var(--primary)"><?= $stats['ativos'] ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Faturas Pendentes</div>
            <div class="stat-val" style="color:var(--danger)"><?= $stats['pendentes'] ?></div>
        </div>
    </div>

    <section class="finance-card" style="margin-top:30px;">
        <h3 style="margin-bottom:20px; font-size:16px;"><i class="fa-solid fa-file-contract"></i> Gestão de Contratos</h3>
        <table class="bt-table" width="100%">
            <thead>
                <tr>
                    <th align="left">Empresa / Instalação</th>
                    <th align="center">Licença</th>
                    <th align="center">Plano</th>
                    <th align="right">Valor</th>
                    <th align="center">Status</th>
                    <th align="right">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data['licencas'] as $l): ?>
                <tr>
                    <td>
                        <div style="font-weight:bold;"><?= htmlspecialchars($l['empresa_nome']) ?></div>
                        <div style="font-size:11px; color:var(--text2)"><?= htmlspecialchars($l['instalacao_nome']) ?></div>
                    </td>
                    <td align="center"><span class="badge" style="background:#333"><?= $l['licenca_tipo'] ?></span></td>
                    <td align="center"><?= $l['plano_nome'] ?? '<span style="color:#666">Nenhum</span>' ?></td>
                    <td align="right" class="money"><?= $l['valor_mensal'] ? 'R$ '.number_format((float) $l['valor_mensal'], 2, ',', '.') : '--' ?></td>
                    <td align="center">
                        <?php if($l['contrato_id']): ?>
                            <span class="badge <?= $l['contrato_status'] === 'ATIVO' ? 'success' : 'warning' ?>"><?= $l['contrato_status'] ?></span>
                        <?php else: ?>
                            <span style="color:var(--danger); font-size:11px;">❌ SEM CONTRATO</span>
                        <?php endif; ?>
                    </td>
                    <td align="right">
                        <button class="bt-button <?= $l['contrato_id'] ? '' : 'bt-primary' ?>"
                                onclick='abrirModalContrato(<?= json_encode($l) ?>)'>
                            <i class="fa-solid <?= $l['contrato_id'] ? 'fa-edit' : 'fa-plus' ?>"></i>
                            <?= $l['contrato_id'] ? 'Editar Contrato' : 'Criar Contrato' ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <div class="finance-grid">
        <section class="finance-card">
            <h3 style="margin-bottom:20px; font-size:16px;">🏢 MRR por Empresa</h3>
            <table class="bt-table" width="100%">
                <thead>
                    <tr><th align="left">Empresa</th><th align="center">Licenças</th><th align="right">Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach($data['por_empresa'] as $row): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['empresa']) ?></strong></td>
                        <td align="center"><?= $row['total_licencas'] ?></td>
                        <td align="right" class="money">R$ <?= number_format((float) $row['total_mensal'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="finance-card">
            <h3 style="margin-bottom:20px; font-size:16px;">💳 Recebimentos Recentes</h3>
            <table class="bt-table" width="100%">
                <thead>
                    <tr><th align="left">Cliente</th><th align="center">Mês</th><th align="right">Valor</th><th align="center">Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach($data['ultimos_pagamentos'] as $p): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['empresa']) ?></strong></td>
                        <td align="center"><?= $p['competencia'] ?></td>
                        <td align="right">R$ <?= number_format((float) ($p['valor_pago'] ?? $p['valor_cobrado']), 2, ',', '.') ?></td>
                        <td align="center"><span class="badge <?= $p['status'] === 'PAGO' ? 'success' : 'warning' ?>"><?= $p['status'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

</main>

<!-- MODAL DE CONTRATO -->
<div id="modalContrato" class="bt-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Novo Contrato Financeiro</h3>
            <button onclick="fecharModal()" style="background:none; border:none; color:white; cursor:pointer; font-size:20px;">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="salvar_contrato">
            <input type="hidden" name="licenca_id" id="f_licenca_id">
            <input type="hidden" name="contrato_id" id="f_contrato_id">

            <div class="form-group">
                <label>Empresa / Instalação</label>
                <div id="f_identidade" style="padding:10px; background:var(--sidebar); border-radius:8px; margin-top:5px; font-size:13px;"></div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top:15px;">
                <div class="form-group">
                    <label>Plano</label>
                    <select name="plano_id" id="f_plano_id" class="form-control" onchange="atualizarValorPlano()" required>
                        <option value="">Selecione...</option>
                        <?php foreach($data['planos'] as $plano): ?>
                            <option value="<?= $plano['id'] ?>" data-valor="<?= $plano['valor_base'] ?>"><?= $plano['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Valor Mensal</label>
                    <input type="number" step="0.01" name="valor_mensal" id="f_valor_mensal" class="form-control" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top:15px;">
                <div class="form-group">
                    <label>Dia de Vencimento</label>
                    <input type="number" min="1" max="31" name="dia_vencimento" id="f_dia_vencimento" class="form-control" value="10" required>
                </div>
                <div class="form-group">
                    <label>Forma de Pagamento</label>
                    <select name="forma_pagamento" id="f_forma_pagamento" class="form-control">
                        <option value="PIX">PIX</option>
                        <option value="BOLETO">BOLETO</option>
                        <option value="CARTAO">CARTÃO</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top:15px;">
                <div class="form-group">
                    <label>Primeiro Vencimento</label>
                    <input type="date" name="data_inicio" id="f_data_inicio" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Status do Contrato</label>
                    <select name="status" id="f_status" class="form-control">
                        <option value="ATIVO">ATIVO</option>
                        <option value="SUSPENSO">SUSPENSO</option>
                        <option value="CANCELADO">CANCELADO</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label><input type="checkbox" name="renovacao_automatica" id="f_renovacao" value="1" checked> Renovação Automática</label>
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label>Observações Internas</label>
                <textarea name="observacao" id="f_observacao" class="form-control" rows="2" placeholder="Notas sobre o contrato..."></textarea>
            </div>

            <div style="margin-top:25px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="bt-button" onclick="fecharModal()">Cancelar</button>
                <button type="submit" class="bt-button bt-primary">Salvar Contrato</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('modalContrato');

    function abrirModalContrato(dados) {
        document.getElementById('f_licenca_id').value = dados.licenca_id;
        document.getElementById('f_contrato_id').value = dados.contrato_id || '';
        document.getElementById('f_identidade').innerHTML = `<strong>${dados.empresa_nome}</strong><br>${dados.instalacao_nome} <small>(${dados.licenca_tipo})</small>`;

        if (dados.contrato_id) {
            document.getElementById('modalTitle').innerText = 'Editar Contrato';
            document.getElementById('f_plano_id').value = dados.plano_id || '';
            document.getElementById('f_valor_mensal').value = dados.valor_mensal || '';
            document.getElementById('f_dia_vencimento').value = dados.dia_vencimento || 10;
            document.getElementById('f_status').value = dados.contrato_status;
            document.getElementById('f_forma_pagamento').value = dados.forma_pagamento || 'PIX';
            document.getElementById('f_data_inicio').value = dados.data_inicio || '<?= date('Y-m-d') ?>';
            document.getElementById('f_renovacao').checked = (dados.renovacao_automatica == 1);
            document.getElementById('f_observacao').value = dados.observacao || '';
        } else {
            document.getElementById('modalTitle').innerText = 'Criar Novo Contrato';
            document.getElementById('f_plano_id').value = '';
            document.getElementById('f_valor_mensal').value = '';
            document.getElementById('f_status').value = 'ATIVO';
            document.getElementById('f_renovacao').checked = true;
            document.getElementById('f_observacao').value = '';
        }

        modal.style.display = 'block';
    }

    function fecharModal() { modal.style.display = 'none'; }

    function atualizarValorPlano() {
        const select = document.getElementById('f_plano_id');
        const valor = select.options[select.selectedIndex].getAttribute('data-valor');
        if (valor) document.getElementById('f_valor_mensal').value = valor;
    }

    window.onclick = function(event) { if (event.target == modal) fecharModal(); }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
