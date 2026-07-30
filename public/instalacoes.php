<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\App\Instalacao\InstalacaoController;

$controller = new InstalacaoController();

if (isset($_GET['delete'])) {
    $controller->excluir((int)$_GET['delete']);
    header('Location: instalacoes.php?deleted=1');
    exit;
}

if (isset($_GET['generate_pin'])) {
    $controller->gerarCodigoAtivacao((int)$_GET['generate_pin']);
    header('Location: instalacoes.php?pin_ok=1');
    exit;
}

$instalacoes = $controller->listarComDispositivos();

$pageTitle = 'Monitoramento de Instalações';
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; display: inline-flex; align-items: center; gap: 6px; }
    .status-online { background: rgba(24, 201, 100, 0.1); color: #18C964; border: 1px solid rgba(24, 201, 100, 0.2); }
    .status-offline { background: rgba(255, 77, 77, 0.1); color: #FF4D4D; border: 1px solid rgba(255, 77, 77, 0.2); }
    .version-tag { background: var(--sidebar); color: var(--text2); padding: 2px 8px; border-radius: 4px; font-size: 12px; font-family: monospace; }
    .device-count { background: var(--primary); color: #fff; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
</style>

<main class="bt-main">

    <section class="bt-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <div>
                <h2 style="margin:0;">🚀 Monitoramento de Instalações</h2>
                <p style="color:var(--text2); font-size:14px; margin-top:5px;">Visão operacional de todos os pontos ativos no ecossistema.</p>
            </div>
            <a href="instalacao.php" class="bt-button bt-primary">
                ➕ Nova Instalação
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="bt-alert bt-success" style="margin-bottom: 20px;">
                ✅ Instalação e dados vinculados removidos com sucesso!
            </div>
        <?php endif; ?>

        <table class="bt-table">
            <thead>
                <tr>
                    <th>Empresa / Unidade</th>
                    <th>Identidade</th>
                    <th align="center">Versão</th>
                    <th align="center">Status</th>
                    <th align="center">Dispositivos</th>
                    <th align="right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($instalacoes as $item): ?>
                <tr>
                    <td>
                        <div style="font-weight:bold; color:var(--text);"><?= htmlspecialchars($item['empresa']) ?></div>
                        <div style="font-size:12px; color:var(--text2);"><?= htmlspecialchars($item['nome']) ?></div>
                    </td>
                    <td>
                        <div style="font-size:11px; color:var(--text2); font-family:monospace;">UUID: <?= substr($item['uuid'], 0, 18) ?>...</div>
                        <div style="font-size:11px; color:var(--secondary);">Produto: <?= htmlspecialchars($item['produto']) ?></div>
                        <?php if ($item['codigo_ativacao']): ?>
                            <div style="margin-top:5px; font-size:11px; color:#f5a524; font-weight:bold;">
                                🔑 Ativação: <span style="background:rgba(245,165,36,0.1); padding:1px 5px; border-radius:3px;"><?= $item['codigo_ativacao'] ?></span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td align="center">
                        <span class="version-tag">v<?= htmlspecialchars($item['versao'] ?? '1.0.0') ?></span>
                    </td>
                    <td align="center">
                        <?php
                            $isOnline = false;
                            if ($item['ultima_sincronizacao'] ?? null) {
                                // Threshold de 30 minutos (1800 segundos)
                                $isOnline = (time() - strtotime($item['ultima_sincronizacao'])) < 1800;
                            }
                        ?>
                        <?php if ($isOnline): ?>
                            <span class="status-badge status-online"><div style="width:6px; height:6px; background:#18C964; border-radius:50%;"></div> ONLINE</span>
                        <?php else: ?>
                            <span class="status-badge status-offline"><div style="width:6px; height:6px; background:#FF4D4D; border-radius:50%;"></div> OFFLINE</span>
                        <?php endif; ?>
                    </td>
                    <td align="center">
                        <div style="display:flex; justify-content:center;">
                            <div class="device-count" title="Dispositivos vinculados"><?= $item['total_dispositivos'] ?? 0 ?></div>
                        </div>
                    </td>
                    <td align="right">
                        <div style="display:flex; gap:10px; justify-content:flex-end;">
                            <a href="instalacoes.php?generate_pin=<?= $item['id'] ?>" class="bt-button" style="padding: 8px 15px; font-size:13px; background:#f5a524; color:#fff;" title="Gerar novo PIN de ativação">🔑 PIN</a>
                            <a href="instalacao.php?id=<?= $item['id'] ?>" class="bt-button" style="padding: 8px 15px; font-size:13px;">✏️ Editar</a>
                            <a href="instalacoes.php?delete=<?= $item['id'] ?>" class="bt-button" style="padding: 8px 15px; font-size:13px; color:var(--danger);" onclick="return confirm('Excluir instalação?')">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
