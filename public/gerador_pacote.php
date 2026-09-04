<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\App\Auth\Auth;
use BT\App\Updates\PackageController;

Auth::requireAdmin();

$produto = $_GET['p'] ?? $_POST['produto'] ?? 'BT_QUEUE_ENTERPRISE';
$controller = new PackageController($produto);
$info = $controller->getVersionInfo();

// Sugestão de próxima versão
$vParts = explode('.', $info['version']);
if (count($vParts) === 3) {
    $vParts[2] = (int)$vParts[2] + 1;
}
$nextVersion = implode('.', $vParts);

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova_versao'])) {
    $result = $controller->generate(trim($_POST['nova_versao']));
    $controller->cleanOldPackages(); // Limpeza preventiva
}

$pageTitle = 'Gerador de Pacotes OTA';
require_once __DIR__ . '/includes/header.php';
?>

<main class="bt-main">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <div>
            <h2 style="margin:0;">📦 Gerador de Pacotes Automático</h2>
            <p style="color:var(--text2); font-size:14px; margin-top:5px;">Crie arquivos ZIP de atualização da Enterprise com um único clique.</p>
        </div>
    </div>

    <?php if($result && $result['success']): ?>
        <div class="bt-alert bt-success" style="margin-bottom: 25px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong>✅ Pacote v<?= $result['version'] ?> gerado com sucesso!</strong><br>
                    O arquivo já contém as pastas <code>core/</code> e <code>public/</code> atualizadas.
                </div>
                <a href="<?= $result['download_url'] ?>" class="bt-button" style="background:#fff; color:var(--success); font-weight:bold;">
                    <i class="fa-solid fa-download"></i> BAIXAR AGORA
                </a>
            </div>
        </div>
    <?php elseif($result): ?>
        <div class="bt-alert bt-danger" style="margin-bottom: 25px;">
            ❌ Erro ao gerar pacote: <?= $result['message'] ?>
        </div>
    <?php endif; ?>

    <div class="bt-grid" style="grid-template-columns: 1fr 1fr;">

        <section class="bt-card">
            <h3>Informações do Sistema</h3>
            <div style="margin-top:20px;">
                <label style="font-size:12px; color:var(--text2);">VERSÃO ATUAL NA PASTA:</label>
                <div style="font-size:32px; font-weight:bold; color:var(--secondary);">v<?= $info['version'] ?></div>
                <p style="font-size:11px; color:var(--text2); margin-top:5px;">Build: <?= $info['build'] ?></p>
            </div>

            <hr style="margin:25px 0; border:0; border-top:1px solid var(--border);">

            <form method="POST">
                <div class="form-group">
                    <label>Produto / Plataforma</label>
                    <select name="produto" class="form-control" onchange="location.href='?p=' + this.value">
                        <option value="BT_QUEUE_ENTERPRISE" <?= $produto === 'BT_QUEUE_ENTERPRISE' ? 'selected' : '' ?>>🎫 BT Queue Enterprise (Farmácia)</option>
                        <option value="BT_QUEUE_ENTERPRISE_LITE" <?= $produto === 'BT_QUEUE_ENTERPRISE_LITE' ? 'selected' : '' ?>>💈 BT Queue Lite (SaaS)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label>Número da Nova Versão</label>
                    <input type="text" name="nova_versao" class="form-control" value="<?= $nextVersion ?>" required>
                    <p style="font-size:11px; color:var(--text2); margin-top:5px;">O sistema atualizará automaticamente o <code>version.json</code> da pasta selecionada.</p>
                </div>

                <button type="submit" class="bt-button bt-primary" style="width:100%; margin-top:25px; padding:15px;">
                    <i class="fa-solid fa-file-zipper"></i> GERAR E BAIXAR PACOTE
                </button>
            </form>
        </section>

        <section class="bt-card">
            <h3>Como funciona o Gerador?</h3>
            <ul style="margin-top:20px; color:var(--text2); font-size:14px; line-height:1.6;">
                <li>🚀 <b>Padrão Profissional</b>: O ZIP é montado exatamente com as pastas <code>core/</code> e <code>public/</code>.</li>
                <li>🛡️ <b>Segurança de Dados</b>: O banco de dados (<code>banco.db</code>) é <b>ignorado</b> automaticamente para não apagar dados dos clientes.</li>
                <li>🧹 <b>Limpeza de Lixo</b>: Pastas de <code>cache/</code> e <code>logs/</code> são removidas do pacote para deixá-lo leve.</li>
                <li>📝 <b>Versão Automática</b>: Você não precisa mais editar o arquivo de versão manualmente.</li>
            </ul>
        </section>

    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
