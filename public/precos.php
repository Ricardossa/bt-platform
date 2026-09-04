<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';
require_once __DIR__ . '/../bootstrap/autoload.php';

use BT\App\Planos\PlanosController;

$controller = new PlanosController();
$planos = $controller->listar();

$pageTitle = 'Tabela de Preços e Planos';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .plan-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; margin-top: 30px; }
    .plan-card { background: var(--card); border: 1px solid var(--border); border-radius: 15px; padding: 30px; position: relative; }
    .plan-card h3 { font-size: 20px; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .plan-card p { font-size: 14px; color: var(--text2); margin-bottom: 15px; line-height: 1.6; }
    .plan-card ul { list-style: none; margin-bottom: 25px; }
    .plan-card li { font-size: 13px; color: var(--text2); margin-bottom: 8px; display: flex; align-items: flex-start; gap: 10px; }
    .plan-card li i { color: var(--secondary); margin-top: 4px; }

    .price-badge { background: var(--sidebar); border: 1px solid var(--border); border-radius: 12px; padding: 20px; text-align: center; }
    .price-badge .main-val { font-size: 28px; font-weight: 900; color: #fff; }
    .price-badge .sub-val { font-size: 14px; color: var(--text3); }
</style>

<main class="bt-main">

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="margin:0;">🚀 Ofertas Comerciais — BT Platform</h2>
            <p style="color:var(--text2); font-size:14px;">Tabela dinâmica de pacotes e assinaturas.</p>
        </div>
        <a href="planos_gerenciar.php" class="bt-button bt-primary">
            <i class="fa-solid fa-gear"></i> CONFIGURAR OFERTAS
        </a>
    </div>

    <div class="plan-container">
        <?php foreach ($planos as $p): ?>
        <?php if($p['status'] !== 'ATIVO') continue; ?>
        <div class="plan-card">
            <h3><?= $p['nome'] ?></h3>
            <p>Limite: <b><?= $p['max_dispositivos'] ?></b> dispositivos ativos.</p>

            <div style="font-size:11px; font-weight:bold; color:var(--text3); margin-bottom:10px; text-transform:uppercase;">Recursos Inclusos:</div>
            <ul>
                <?php
                    $recursos = explode(',', $p['recursos']);
                    foreach($recursos as $r):
                ?>
                    <li><i class="fa-solid fa-check"></i> <?= trim($r) ?></li>
                <?php endforeach; ?>
            </ul>

            <div class="price-badge">
                <div class="main-val">R$ <?= number_format((float)$p['preco_mensal'], 2, ',', '.') ?><small style="font-size:14px; font-weight:400;">/mês</small></div>
                <div class="sub-val">Taxa de Setup: R$ <?= number_format((float)$p['taxa_setup'], 2, ',', '.') ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
