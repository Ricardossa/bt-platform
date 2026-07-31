<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\App\Updates\UpdateController;
use BT\App\Auth\Auth;

Auth::requireAdmin();

$controller = new UpdateController();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = $controller->publicar($_POST, $_FILES['pacote']);
    if ($res['success']) $success = $res['message'];
    else $error = $res['message'];
}

$historico = $controller->listar();
$pageTitle = 'Gestão de Atualizações OTA';

require_once __DIR__ . '/includes/header.php';
?>

<main class="bt-main">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <div>
            <h2 style="margin:0;">🚀 Publicar Nova Versão</h2>
            <p style="color:var(--text2); font-size:14px; margin-top:5px;">Envie pacotes de atualização para toda a frota Enterprise.</p>
        </div>
    </div>

    <?php if($success): ?> <div class="bt-alert bt-success">✅ <?= $success ?></div> <?php endif; ?>
    <?php if($error): ?> <div class="bt-alert bt-danger">❌ <?= $error ?></div> <?php endif; ?>

    <div class="bt-grid" style="grid-template-columns: 1.2fr 1.8fr;">

        <!-- FORMULÁRIO DE PUBLICAÇÃO -->
        <section class="bt-card">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Produto de Atualização</label>
                    <select name="produto" class="form-control" required>
                        <option value="BT_QUEUE_ENTERPRISE">🎫 BT Queue Enterprise (ZIP)</option>
                        <option value="BT1_COLETOR_PRO">📱 BT1 Coletor Pro (APK)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label>Número da Versão (ex: 4.3.0)</label>
                    <input type="text" name="versao" class="form-control" placeholder="X.X.X" required>
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label>Pacote (.zip para Enterprise / .apk para Coletor)</label>
                    <input type="file" name="pacote" class="form-control" required>
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label>O que mudou? (Changelog)</label>
                    <textarea name="changelog" class="form-control" style="height:100px;" placeholder="Descreva as melhorias..."></textarea>
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label>
                        <input type="checkbox" name="is_mandatory">
                        <b style="color:var(--danger);">Atualização Obrigatória</b>
                    </label>
                    <p style="font-size:11px; color:var(--text2);">Se marcado, o cliente será forçado a atualizar para continuar usando.</p>
                </div>

                <button type="submit" class="bt-button bt-primary" style="width:100%; margin-top:25px;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> PUBLICAR PARA CLIENTES
                </button>
            </form>
        </section>

        <!-- HISTÓRICO DE VERSÕES -->
        <section class="bt-card">
            <h3 style="font-size:16px; margin-bottom:20px;">📋 Histórico de Lançamentos</h3>
            <table class="bt-table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Versão</th>
                        <th>Data</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($historico as $up): ?>
                    <tr>
                        <td>
                            <span style="font-size:11px; font-weight:bold; color:var(--primary);">
                                <?= $up['produto'] === 'BT1_COLETOR_PRO' ? '📱 COLETOR' : '🎫 ENTERPRISE' ?>
                            </span>
                        </td>
                        <td><b style="color:var(--secondary);">v<?= $up['versao'] ?></b></td>
                        <td style="font-size:12px;"><?= date('d/m/Y H:i', strtotime($up['created_at'])) ?></td>
                        <td>
                            <?= $up['is_mandatory'] ? '<span class="badge danger">Obrigatória</span>' : '<span class="badge success">Opcional</span>' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($historico)): ?>
                        <tr><td colspan="4" align="center" style="padding:40px; color:var(--text2);">Nenhuma versão publicada ainda.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
