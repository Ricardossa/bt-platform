<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\Core\Database\Database;

$pageTitle = 'Monitoramento de Unidades SaaS';

// [LITE v3.2.5] Lógica de Exclusão SaaS
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    Database::execute("DELETE FROM instalacoes WHERE id = ?", [$id]);
    header('Location: unidades.php?ok=deleted');
    exit;
}

try {
    $unidades = Database::fetchAll("
        SELECT
            i.id,
            e.nome_fantasia as empresa,
            i.nome as unidade,
            i.uuid,
            i.token,
            i.produto,
            i.versao,
            i.status as status_instalacao,
            i.ultima_sincronizacao,
            i.codigo_ativacao as pin,
            (SELECT status FROM licencas WHERE instalacao_id = i.id ORDER BY id DESC LIMIT 1) as status_licenca,
            (SELECT COUNT(*) FROM dispositivos d WHERE d.instalacao_id = i.id AND d.ativo = 1) as total_dispositivos
        FROM instalacoes i
        LEFT JOIN empresas e ON e.id = i.empresa_id
        ORDER BY i.ultima_sincronizacao DESC, i.id DESC
    ");
} catch (\Throwable $e) {
    // Debug Error
    if (isset($_GET['debug_query'])) {
        echo "<pre>QUERY ERROR: " . htmlspecialchars($e->getMessage()) . "</pre>";
    }

    // Fallback para query básica se colunas novas faltarem
    $unidades = Database::fetchAll("
        SELECT
            i.id,
            i.nome as unidade,
            i.uuid,
            i.token,
            i.produto,
            i.versao,
            i.ultima_sincronizacao,
            e.nome_fantasia as empresa,
            (SELECT COUNT(*) FROM dispositivos d WHERE d.instalacao_id = i.id AND d.ativo = 1) as total_dispositivos,
            'UNKNOWN' as status_licenca,
            '' as pin
        FROM instalacoes i
        LEFT JOIN empresas e ON e.id = i.empresa_id
    ");
}

require_once __DIR__ . '/includes/header.php';

function getRealStatus(?string $lastSync): string {
    if (!$lastSync) return 'OFFLINE';
    $last = strtotime($lastSync);
    $diff = time() - $last;
    return ($diff < 900) ? 'ONLINE' : 'OFFLINE'; // 15 minutos de tolerância (suporta atrasos normais)
}
?>

<main class="bt-main">
    <section class="bt-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <div>
                <h2>🚀 Monitoramento de Unidades SaaS</h2>
                <p style="color:var(--text2); font-size:14px;">Visão operacional de todos os pontos ativos no ecossistema.</p>
            </div>
            <a href="instalacao.php" class="bt-button bt-primary">
                ➕ Nova Unidade / Instalação
            </a>
        </div>

        <table class="bt-table" width="100%">
            <thead>
                <tr>
                    <th>Empresa / Unidade</th>
                    <th>Identidade & Acesso</th>
                    <th>Versão</th>
                    <th>Status Real</th>
                    <th style="text-align:center;">Dispositivos</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unidades as $u): ?>
                <?php
                    $realStatus = getRealStatus($u['ultima_sincronizacao']);
                    $statusColor = ($realStatus === 'ONLINE') ? 'var(--success)' : 'var(--text3)';
                ?>
                <tr>
                    <td>
                        <b><?= htmlspecialchars($u['empresa'] ?? 'N/A') ?></b><br>
                        <span style="color:var(--secondary); font-weight:bold; font-size:12px;"><?= htmlspecialchars($u['unidade']) ?></span>
                    </td>
                    <td>
                        <div style="font-size:10px; color:var(--text2); font-family:monospace; line-height:1.4;">
                            UUID: <?= substr($u['uuid'], 0, 20) ?>...<br>
                            Produto: <b style="color:#fff;"><?= $u['produto'] ?? 'N/A' ?></b><br>
                            <?php if(isset($u['tenant_slug']) && $u['tenant_slug']): ?>
                                URL: <a href="http://<?= $u['tenant_slug'] ?>.brandaotech.com.br" target="_blank" style="color:var(--secondary)"><?= $u['tenant_slug'] ?>.brandaotech...</a>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><span class="badge" style="background:rgba(255,255,255,0.05); color:#fff;">v<?= $u['versao'] ?></span></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <?php $realStatus = getRealStatus($u['ultima_sincronizacao'] ?? null); ?>
                            <?php $statusColor = ($realStatus === 'ONLINE') ? 'var(--success)' : 'var(--text3)'; ?>
                            <div style="width:8px; height:8px; border-radius:50%; background:<?= $statusColor ?>; box-shadow: 0 0 10px <?= $statusColor ?>;"></div>
                            <b style="color:<?= $statusColor ?>; font-size:12px;"><?= $realStatus ?></b>
                        </div>
                        <small style="font-size:9px; color:var(--text3);">Sinc: <?= ($u['ultima_sincronizacao'] ?? null) ? date('d/m H:i', strtotime($u['ultima_sincronizacao'])) : 'Nunca' ?></small>
                    </td>
                    <td align="center">
                        <?php
                            $dispCount = (int)($u['total_dispositivos'] ?? 0);
                            // Se for Appliance (Lite/Enterprise) e sinc recente, mostra pelo menos 1 (Auto-registro)
                            if ($dispCount === 0 && ($u['ultima_sincronizacao'] ?? null)) {
                                $lastS = strtotime($u['ultima_sincronizacao']);
                                if (time() - $lastS < 900) $dispCount = 1;
                            }
                        ?>
                        <div style="font-size:22px; font-weight:900; color:var(--secondary);"><?= $dispCount ?></div>
                    </td>
                    <td>
                        <div style="display:flex; gap:8px;">
                            <button onclick="alert('PIN de Ativação: <?= $u['pin'] ?? 'N/A' ?>')" class="bt-button" title="Ver PIN" style="padding:5px 10px; background:#F5A623; color:#000;">
                                <i class="fa-solid fa-key"></i>
                            </button>
                            <a href="instalacao.php?id=<?= $u['id'] ?>" class="bt-button" title="Editar" style="padding:5px 10px;">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <a href="unidades.php?delete=<?= $u['id'] ?>" class="bt-button bt-danger" onclick="return confirm('Deseja remover esta unidade?')" style="padding:5px 10px;">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<?php
require_once __DIR__ . '/includes/footer.php';
