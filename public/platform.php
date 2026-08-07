<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\App\Platform\PlatformController;
use BT\Core\Upload\Upload;

$controller = new PlatformController();

// Buscamos os dados e usamos uma variável exclusiva para não dar conflito com o header
$platformDados = $controller->buscar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $logo = $platformDados['logo'] ?? null;

    if (
        isset($_FILES['logo']) &&
        $_FILES['logo']['error'] === UPLOAD_ERR_OK
    ) {
        $novaLogo = Upload::image(
            $_FILES['logo'],
            BT_ROOT . '/public/uploads/logo'
        );

        if ($novaLogo !== null) {
            $logo = $novaLogo;
        }
    }

    $controller->salvar([
        'nome_empresa'   => trim($_POST['nome_empresa'] ?? ''),
        'razao_social'   => trim($_POST['razao_social'] ?? ''),
        'cnpj'           => trim($_POST['cnpj'] ?? ''),
        'telefone'       => trim($_POST['telefone'] ?? ''),
        'whatsapp'       => trim($_POST['whatsapp'] ?? ''),
        'email'          => trim($_POST['email'] ?? ''),
        'instagram'      => trim($_POST['instagram'] ?? ''),
        'site'           => trim($_POST['site'] ?? ''),
        'versao_sistema' => trim($_POST['versao_sistema'] ?? ''),
        'tema'           => $_POST['tema'] ?? 'blue',
        'logo'           => $logo,
        'backup_path'    => trim($_POST['backup_path'] ?? ''),
        'backup_retention' => (int)($_POST['backup_retention'] ?? 7),
        'backup_host'    => trim($_POST['backup_host'] ?? ''),
        'backup_user'    => trim($_POST['backup_user'] ?? ''),
        'backup_pass'    => $_POST['backup_pass'] ?? ''
    ]);

    header('Location: platform.php?ok=1');
    exit;
}

$pageTitle = 'Configuração da Plataforma';

require_once __DIR__ . '/includes/header.php';
?>

<main class="bt-main">

<section class="bt-card">

<h2>⚙️ Configuração da Plataforma</h2>

<?php if (isset($_GET['ok'])): ?>
<div class="bt-alert bt-success">
    ✅ Configurações salvas com sucesso.
</div>
<?php endif; ?>

<p style="color:var(--text2); font-size:14px; margin-bottom:20px;">
    Gerencie a identidade visual, informações corporativas e versões da BT Queue Platform.
</p>

<hr style="border:0; border-top:1px solid var(--border); margin:20px 0;">

<form method="POST" enctype="multipart/form-data">

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div class="form-group">
            <label>Nome Fantasia (Exibido no Painel)</label>
            <input type="text" name="nome_empresa" class="form-control" value="<?= htmlspecialchars($platformDados['nome_empresa'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Versão do Sistema</label>
            <input type="text" name="versao_sistema" class="form-control" placeholder="v2.0.0" value="<?= htmlspecialchars($platformDados['versao_sistema'] ?? '') ?>">
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-top:15px;">
        <div class="form-group">
            <label>Razão Social</label>
            <input type="text" name="razao_social" class="form-control" value="<?= htmlspecialchars($platformDados['razao_social'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>CNPJ</label>
            <input type="text" name="cnpj" class="form-control" value="<?= htmlspecialchars($platformDados['cnpj'] ?? '') ?>">
        </div>
    </div>

    <h3 style="margin:30px 0 15px; font-size:16px; color:var(--secondary);">📞 Canais de Contato e Suporte</h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
        <div class="form-group">
            <label>Telefone</label>
            <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($platformDados['telefone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>WhatsApp</label>
            <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($platformDados['whatsapp'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Instagram</label>
            <input type="text" name="instagram" class="form-control" placeholder="@username" value="<?= htmlspecialchars($platformDados['instagram'] ?? '') ?>">
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top:15px;">
        <div class="form-group">
            <label>E-mail Oficial</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($platformDados['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Site / URL</label>
            <input type="text" name="site" class="form-control" value="<?= htmlspecialchars($platformDados['site'] ?? '') ?>">
        </div>
    </div>

    <h3 style="margin:30px 0 15px; font-size:16px; color:var(--secondary);">🎨 Branding e Tema</h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
            <label>Tema Visual</label>
            <select name="tema" class="form-control">
                <option value="blue" <?= ($platformDados['tema'] ?? 'blue') === 'blue' ? 'selected' : '' ?>>🔵 Azul Standard</option>
                <option value="dark" <?= ($platformDados['tema'] ?? 'blue') === 'dark' ? 'selected' : '' ?>>🌑 Escuro High-Contrast</option>
                <option value="light" <?= ($platformDados['tema'] ?? 'blue') === 'light' ? 'selected' : '' ?>>⚪ Claro Business</option>
            </select>
        </div>
        <div class="form-group">
            <label>Logo da Plataforma</label>
            <input type="file" name="logo" class="form-control" accept=".png,.jpg,.jpeg,.webp">
            <?php if (!empty($platformDados['logo'])): ?>
                <p style="margin-top:8px; font-size:12px; color:var(--text2);">Logo atual: <strong><?= htmlspecialchars($platformDados['logo']) ?></strong></p>
            <?php endif; ?>
        </div>
    </div>

    <h3 style="margin:30px 0 15px; font-size:16px; color:var(--secondary);">🛡️ Backup e Segurança (Master -> TrueNAS)</h3>
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
        <div class="form-group">
            <label>IP do Servidor TrueNAS</label>
            <input type="text" name="backup_host" class="form-control" placeholder="192.168.100.250" value="<?= htmlspecialchars($platformDados['backup_host'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Caminho de Rede (SMB Share)</label>
            <input type="text" name="backup_path" class="form-control" placeholder="//192.168.100.250/backups" value="<?= htmlspecialchars($platformDados['backup_path'] ?? '') ?>">
        </div>
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top:15px;">
        <div class="form-group">
            <label>Usuário de Rede</label>
            <input type="text" name="backup_user" class="form-control" value="<?= htmlspecialchars($platformDados['backup_user'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Senha de Rede</label>
            <input type="password" name="backup_pass" class="form-control" value="<?= htmlspecialchars($platformDados['backup_pass'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Retenção (Dias)</label>
            <input type="number" name="backup_retention" class="form-control" value="<?= (int)($platformDados['backup_retention'] ?? 7) ?>" min="1" max="30">
        </div>
    </div>
    <small style="color:var(--text2); display:block; margin-top:10px;">O sistema tentará montar este diretório automaticamente no caminho <code>/mnt/truenas/backups/BKP-MASTER</code> antes de cada backup.</small>

    <div style="margin-top:35px; border-top:1px solid var(--border); padding-top:25px; display: flex; justify-content: space-between;">
        <button type="submit" class="bt-button bt-primary" style="padding: 15px 40px;">
            💾 Salvar Configurações
        </button>
        <button type="button" id="btnTestarBackup" class="bt-button" style="background: var(--sidebar); border: 1px solid var(--border); padding: 15px 30px;">
            📡 Testar Conexão TrueNAS
        </button>
    </div>

</form>

<script>
document.getElementById('btnTestarBackup').addEventListener('click', async () => {
    const btn = document.getElementById('btnTestarBackup');
    btn.innerHTML = '⌛ Testando...';
    btn.disabled = true;

    try {
        const res = await fetch('api/v1/test_backup_mount.php');
        const json = await res.json();
        alert(json.message + (json.output ? "\n\nLog: " + json.output : ""));
    } catch(e) {
        alert("Erro ao disparar teste.");
    } finally {
        btn.innerHTML = '📡 Testar Conexão TrueNAS';
        btn.disabled = false;
    }
});
</script>

</section>

<section class="bt-card" style="margin-top:30px; background:var(--sidebar);">
    <h2 style="font-size:18px;">📋 Resumo da Identidade</h2>
    <table class="bt-table" width="100%">
        <tr>
            <th width="220">Plataforma</th>
            <td style="font-weight:bold; color:var(--secondary);"><?= htmlspecialchars($platformDados['nome_empresa'] ?? '-') ?></td>
        </tr>
        <tr>
            <th>Versão Ativa</th>
            <td><span class="badge success"><?= htmlspecialchars($platformDados['versao_sistema'] ?? 'v2.0.0') ?></span></td>
        </tr>
        <tr>
            <th>WhatsApp</th>
            <td><?= htmlspecialchars($platformDados['whatsapp'] ?? '-') ?></td>
        </tr>
        <tr>
            <th>Instagram</th>
            <td><?= htmlspecialchars($platformDados['instagram'] ?? '-') ?></td>
        </tr>
        <tr>
            <th>Tema</th>
            <td style="text-transform: capitalize;"><?= htmlspecialchars($platformDados['tema'] ?? 'blue') ?></td>
        </tr>
        <tr>
            <th>Logo</th>
            <td>
                <?php if (!empty($platformDados['logo'])): ?>
                    <img src="/uploads/logo/<?= htmlspecialchars($platformDados['logo']) ?>" style="max-height:80px; max-width: 200px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
                <?php else: ?>
                    <span style="color:var(--text2); font-style:italic;">Nenhuma logo carregada</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</section>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';
