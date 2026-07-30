<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\App\Licenca\LicencaController;

$controller = new LicencaController();

if (isset($_GET['delete'])) {
    $controller->excluir((int) $_GET['delete']);
    header('Location: licencas.php');
    exit;
}

$licencas = $controller->listar();

$pageTitle = 'Gestão de Licenças';
require_once __DIR__ . '/includes/header.php';

function getValidadeStatus(string $data): array {
    $hoje = new DateTime();
    $validade = new DateTime($data);
    $diff = $hoje->diff($validade);
    $days = (int) $diff->format('%r%a');

    if ($days < 0) return ['label' => 'VENCIDA', 'color' => '#FF4D4D', 'bg' => 'rgba(255, 77, 77, 0.1)'];
    if ($days <= 7) return ['label' => 'VENCE EM ' . $days . 'D', 'color' => '#FFC107', 'bg' => 'rgba(255, 193, 7, 0.1)'];
    return ['label' => 'OK', 'color' => '#18C964', 'bg' => 'rgba(24, 201, 100, 0.1)'];
}
?>

<style>
    .plan-badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: bold; }
    .plan-premium { background: #FFD700; color: #000; }
    .plan-pro { background: #1DB4FF; color: #fff; }
    .plan-enterprise { background: #1565C0; color: #fff; }
    .plan-demo { background: #666; color: #fff; }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 8px; }
</style>

<main class="bt-main">

    <section class="bt-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <div>
                <h2 style="margin:0;">🔑 Gestão de Licenças</h2>
                <p style="color:var(--text2); font-size:14px; margin-top:5px;">Controle de contratos, planos e validades do ecossistema.</p>
            </div>
            <a href="licenca.php" class="bt-button bt-primary">
                ➕ Nova Licença
            </a>
        </div>

        <!-- Filtros Rápidos -->
        <div style="display:flex; gap:10px; margin-bottom:25px;">
            <button class="bt-button" style="padding: 8px 15px; font-size:13px; background:var(--sidebar); border:1px solid var(--border);">Todas</button>
            <button class="bt-button" style="padding: 8px 15px; font-size:13px; background:var(--sidebar); border:1px solid var(--border);">🔴 Vencidas</button>
            <button class="bt-button" style="padding: 8px 15px; font-size:13px; background:var(--sidebar); border:1px solid var(--border);">🟡 Vencendo (7d)</button>
        </div>

        <table class="bt-table">
            <thead>
                <tr>
                    <th>Cliente / Instalação</th>
                    <th align="center">Plano</th>
                    <th align="center">Status</th>
                    <th align="center">Data Validade</th>
                    <th align="center">Saúde Contratual</th>
                    <th align="right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($licencas as $item): ?>
                <?php $vStatus = getValidadeStatus($item['data_validade']); ?>
                <tr>
                    <td>
                        <div style="font-weight:bold; color:var(--text);"><?= htmlspecialchars($item['empresa']) ?></div>
                        <div style="font-size:12px; color:var(--text2);"><?= htmlspecialchars($item['instalacao']) ?></div>
                    </td>
                    <td align="center">
                        <?php
                        $pClass = 'plan-enterprise';
                        $pName = $item['tipo'];
                        if ($pName === 'PREMIUM') $pClass = 'plan-premium';
                        if ($pName === 'PRO') $pClass = 'plan-pro';
                        if ($pName === 'DEMONSTRACAO') $pClass = 'plan-demo';
                        ?>
                        <span class="plan-badge <?= $pClass ?>"><?= $pName ?></span>
                    </td>
                    <td align="center">
                        <?php
                        $dotColor = '#18C964';
                        if ($item['status'] !== 'ATIVA') $dotColor = '#FF4D4D';
                        ?>
                        <div style="display:flex; align-items:center; justify-content:center;">
                            <div class="status-dot" style="background:<?= $dotColor ?>"></div>
                            <span style="font-size:13px;"><?= $item['status'] ?></span>
                        </div>
                    </td>
                    <td align="center" style="font-family:monospace; font-size:14px;">
                        <?= date('d/m/Y', strtotime($item['data_validade'])) ?>
                    </td>
                    <td align="center">
                        <span style="padding:4px 12px; border-radius:4px; font-size:11px; font-weight:bold; color:<?= $vStatus['color'] ?>; background:<?= $vStatus['bg'] ?>;">
                            <?= $vStatus['label'] ?>
                        </span>
                    </td>
                    <td align="right">
                        <div style="display:flex; gap:10px; justify-content:flex-end;">
                            <a href="licenca.php?id=<?= $item['id'] ?>" class="bt-button" style="padding: 8px 15px; font-size:13px;">✏️</a>
                            <a href="licencas.php?delete=<?= $item['id'] ?>" class="bt-button" style="padding: 8px 15px; font-size:13px; color:var(--danger);" onclick="return confirm('Excluir licença?')">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
