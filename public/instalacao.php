<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\App\Instalacao\InstalacaoController;
use BT\Core\Database\Database;

$controller = new InstalacaoController();

$id = (int) ($_GET['id'] ?? 0);
$instalacao = null;

if ($id > 0) {
    $instalacao = $controller->buscar($id);
}

$empresas = Database::fetchAll(
    "SELECT id, nome_fantasia FROM empresas ORDER BY nome_fantasia"
);

$produtos = Database::fetchAll(
    "SELECT slug, nome FROM produtos ORDER BY nome ASC"
);

$planos = Database::fetchAll(
    "SELECT id, nome FROM planos WHERE status = 'ATIVO' ORDER BY preco_mensal ASC"
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $dados = [
        'empresa_id' => (int) ($_POST['empresa_id'] ?? 0),
        'plano_id' => (int) ($_POST['plano_id'] ?? 0),
        'produto' => $_POST['produto'] ?? 'BT_QUEUE_ENTERPRISE',
        'nome' => trim($_POST['nome'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'uuid' => $_POST['uuid'] ?? '',
        'versao' => trim($_POST['versao'] ?? '4.0.0'),
        'status' => $_POST['status'] ?? 'ONLINE'
    ];
    
    if ($id > 0) {
        $resultado = $controller->atualizar($id, $dados);
        $newId = $id;
    } else {
        $service = new \BT\App\Instalacao\InstallationProvisioningService();
        $resProvision = $service->provisionar($dados);
        $resultado = $resProvision['success'];
        $newId = $resProvision['instalacao_id'] ?? 0;

        if (!$resultado) {
            $erro = 'Erro ao provisionar: ' . ($resProvision['message'] ?? 'Erro desconhecido.');
        }
    }
    
    if ($resultado && $newId > 0) {
        header("Location: instalacao.php?id=$newId&ok=1");
        exit;
    } elseif (!$erro) {
        $erro = 'Erro ao salvar a instalação. Verifique os dados.';
    }
}

if (isset($_GET['delete_device'])) {
    $controller->removerDispositivo((int)$_GET['delete_device']);
    header("Location: instalacao.php?id=$id&ok=device_deleted");
    exit;
}

$pageTitle = $id > 0 ? 'Editar Instalação' : 'Nova Instalação';

require_once __DIR__ . '/includes/header.php';
?>

<main class="bt-main">

<section class="bt-card">

<h2>🖥️ <?= $id > 0 ? 'Editar' : 'Nova' ?> Instalação</h2>

<?php if (isset($_GET['ok'])): ?>

<div class="bt-alert bt-success">
    ✅ Instalação cadastrada com sucesso!
</div>

<?php endif; ?>

<?php if (isset($erro)): ?>

<div class="bt-alert bt-danger">
    ❌ <?= htmlspecialchars($erro) ?>
</div>

<?php endif; ?>

<p>
Cadastro de uma instalação da BT Queue Enterprise.
</p>

<hr style="margin:20px 0;">

<form method="POST">
<?php if ($id > 0): ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="uuid" value="<?= $instalacao['uuid'] ?>">
<?php endif; ?>

<div class="form-group">
    <label>Empresa *</label>
    <select name="empresa_id" class="form-control" required>
        <option value="">Selecione...</option>
        <?php foreach ($empresas as $empresa): ?>
            <option value="<?= $empresa['id'] ?>" <?= (isset($instalacao['empresa_id']) && $instalacao['empresa_id'] == $empresa['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($empresa['nome_fantasia']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group" style="margin-top:15px;">
    <label>Produto *</label>
    <select name="produto" class="form-control" required>
        <?php foreach ($produtos as $p): ?>
            <option value="<?= $p['slug'] ?>" <?= ($instalacao['produto'] ?? 'BT_QUEUE_ENTERPRISE') === $p['slug'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group" style="margin-top:15px;">
    <label>Pacote Comercial (Assinatura SaaS) *</label>
    <select name="plano_id" class="form-control" required>
        <option value="">Selecione um pacote...</option>
        <?php foreach ($planos as $plano): ?>
            <option value="<?= $plano['id'] ?>" <?= (isset($instalacao['plano_id']) && $instalacao['plano_id'] == $plano['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($plano['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group" style="margin-top:15px;">
    <label>Nome da Instalação / Unidade *</label>
    <input
        type="text"
        name="nome"
        class="form-control"
        required
        value="<?= htmlspecialchars($instalacao['nome'] ?? '') ?>"
        placeholder="Ex: Unidade Centro, Homologação">
</div>

<div class="form-group" style="margin-top:15px;">
    <label>Subdomínio (Exclusivo SaaS) *</label>
    <div style="display:flex; align-items:center; gap:5px;">
        <input
            type="text"
            name="slug"
            class="form-control"
            required
            value="<?= htmlspecialchars($id > 0 ? (Database::fetch("SELECT slug FROM tenants WHERE id = ?", [$id])['slug'] ?? '') : '') ?>"
            placeholder="ex: barber1">
        <span style="color:var(--text2); font-size:12px;">.brandaotech.com.br</span>
    </div>
</div>



<div class="form-group" style="margin-top:15px;">
    <label>Versão Enterprise</label>
    <input
        type="text"
        name="versao"
        class="form-control"
        value="<?= htmlspecialchars($instalacao['versao'] ?? '1.0.0') ?>"
        placeholder="1.0.0">
</div>

<div class="form-group" style="margin-top:15px;">
    <label>Status</label>
    <select name="status" class="form-control">
        <option value="ONLINE" <?= ($instalacao['status'] ?? '') === 'ONLINE' ? 'selected' : '' ?>>🟢 Online</option>
        <option value="OFFLINE" <?= ($instalacao['status'] ?? 'OFFLINE') === 'OFFLINE' ? 'selected' : '' ?>>🔴 Offline</option>
    </select>
</div>

<button
    type="submit"
    class="bt-button bt-primary"
    style="margin-top:25px;">

💾 Salvar Instalação

</button>

<a href="instalacoes.php" class="bt-button" style="margin-top:25px;margin-left:10px;">
    ↩️ Voltar
</a>

</form>

</section>

<?php if ($id > 0 && $instalacao): ?>
    <section class="bt-card" style="margin-top:20px;">
        <h3>📋 Detalhes Técnicos da Instalação</h3>
        <ul style="padding-left:20px; margin-top:15px; display:flex; flex-direction:column; gap:10px; list-style:none;">
            <li><strong style="color:var(--text2); min-width:100px; display:inline-block;">Empresa:</strong> <b style="color:#fff;"><?= htmlspecialchars($instalacao['empresa_nome'] ?? $empresas[array_search($instalacao['empresa_id'], array_column($empresas, 'id'))]['nome_fantasia'] ?? 'N/A') ?></b></li>
            <li><strong style="color:var(--text2); min-width:100px; display:inline-block;">Nome:</strong> <span style="color:#fff;"><?= htmlspecialchars($instalacao['nome']) ?></span></li>
            <li><strong style="color:var(--text2); min-width:100px; display:inline-block;">Produto:</strong> <span class="badge" style="background:var(--secondary); color:#000; font-weight:bold;"><?= htmlspecialchars($instalacao['produto']) ?></span></li>
            <li><strong style="color:var(--text2); min-width:100px; display:inline-block;">UUID:</strong> <code style="color:var(--secondary); font-size:14px; font-weight:bold;"><?= $instalacao['uuid'] ?></code></li>
            <li><strong style="color:var(--text2); min-width:100px; display:inline-block;">TOKEN:</strong> <code style="color:var(--success); font-size:14px; font-weight:bold;"><?= $instalacao['token'] ?></code></li>
            <?php if (!empty($instalacao['codigo_ativacao'])): ?>
                <li><strong style="color:var(--warning); min-width:100px; display:inline-block;">PIN (Ativação):</strong> <code style="background:rgba(245,165,36,0.1); padding:2px 8px; border-radius:4px; color:#f5a524; font-size:16px; font-weight:900; border:1px solid #f5a524;"><?= $instalacao['codigo_ativacao'] ?></code></li>
            <?php endif; ?>
            <li><strong style="color:var(--text2); min-width:100px; display:inline-block;">Versão:</strong> <span style="color:var(--text2);">v<?= htmlspecialchars($instalacao['versao']) ?></span></li>
            <li><strong style="color:var(--text2); min-width:100px; display:inline-block;">Status:</strong>
                <span style="color:<?= $instalacao['status'] === 'ONLINE' ? 'var(--success)' : 'var(--danger)' ?>; font-weight:bold;">
                    <?= $instalacao['status'] ?>
                </span>
            </li>
        </ul>
    </section>

    <section class="bt-card" style="margin-top:20px; border-left: 4px solid var(--secondary);">
        <h3 style="color:var(--secondary);"><i class="fa-solid fa-bolt"></i> Carga Rápida de Provisionamento</h3>
        <p style="color:var(--text2); font-size:13px; margin-bottom:15px;">
            Copie o bloco abaixo e cole no campo "CARGA RÁPIDA" do instalador no cliente.
        </p>

        <?php
        $syncUrl = 'https://api.brandaotech.com.br/api/v1/sync.php';
        $quickLoad = "{$syncUrl}|{$instalacao['uuid']}|{$instalacao['token']}";
        ?>

        <div id="quickLoadBlock" style="background:#0d1b2a; padding:15px; border-radius:8px; font-family:monospace; font-size:12px; color:#fff; word-break:break-all; border:1px solid var(--border);">
            <?= htmlspecialchars($quickLoad) ?>
        </div>

        <div style="margin-top:12px;">
            <button onclick="copyToClipboard('<?= $quickLoad ?>')" class="bt-button bt-secondary">
                <i class="fa-solid fa-copy"></i> Copiar Bloco de Ativação
            </button>
        </div>
    </section>

    <section class="bt-card" style="margin-top:20px;">
        <h3>📱 Dispositivos Vinculados</h3>
        <?php
        $dispositivos = $controller->buscarDispositivos($id);
        ?>

        <?php if (empty($dispositivos)): ?>
            <p style="color:#888; margin-top:15px;">Nenhum dispositivo vinculado a esta instalação.</p>
        <?php else: ?>
            <table class="bt-table" width="100%" style="margin-top:15px;">
                <thead>
                    <tr>
                        <th>Dispositivo</th>
                        <th>Modelo</th>
                        <th>Fabricante</th>
                        <th align="center">Versão</th>
                        <th align="center">Status</th>
                        <th>Última Sinc.</th>
                        <th align="center">Bateria</th>
                        <th align="right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dispositivos as $disp): ?>
                        <tr>
                            <td style="font-size:12px; font-family:monospace; color:var(--secondary);">
                                <?= substr($disp['device_uuid'], 0, 15) ?>...
                            </td>
                            <td><?= htmlspecialchars($disp['modelo'] ?? 'Desconhecido') ?></td>
                            <td><?= htmlspecialchars($disp['fabricante'] ?? 'Desconhecido') ?></td>
                            <td align="center"><span class="badge" style="background:var(--sidebar);"><?= htmlspecialchars($disp['versao_app'] ?? '1.0.0') ?></span></td>
                            <td align="center">
                                <span style="background: <?= $disp['status'] === 'ONLINE' ? '#18C964' : '#666' ?>; color: #fff; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight:bold;">
                                    <?= htmlspecialchars($disp['status'] ?? 'PENDENTE') ?>
                                </span>
                            </td>
                            <td><?= $disp['ultima_sincronizacao'] ? date('d/m/Y H:i', strtotime($disp['ultima_sincronizacao'])) : 'Nunca' ?></td>
                            <td align="center"><?php if (isset($disp['bateria']) && $disp['bateria']): ?>🔋 <?= $disp['bateria'] ?>%<?php else: ?>--<?php endif; ?></td>
                            <td align="right">
                                <a href="instalacao.php?id=<?= $id ?>&delete_device=<?= $disp['id'] ?>"
                                   class="bt-button bt-danger"
                                   style="padding:5px 10px;"
                                   onclick="return confirm('Remover este dispositivo?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
<?php endif; ?>

<script>
function copyToClipboard(text) {
    const el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    alert('Copiado para a área de transferência!');
}
</script>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';
