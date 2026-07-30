<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\App\Licenca\LicencaController;
use BT\Core\Database\Database;

$controller = new LicencaController();

$instalacoes = Database::fetchAll(
    "SELECT
        i.id,
        i.nome,
        i.uuid,
        i.token,
        i.produto,
        e.nome_fantasia AS empresa
     FROM instalacoes i
     INNER JOIN empresas e ON e.id = i.empresa_id
     ORDER BY e.nome_fantasia, i.nome"
);

$licenca = null;
$instalacao = null;

if (isset($_GET['id'])) {
    $licenca = $controller->buscar((int) $_GET['id']);
    
    // Buscar dados da instalação para o QR Code
    if ($licenca) {
        $instalacao = Database::fetch(
            "SELECT i.*, e.nome_fantasia AS empresa
             FROM instalacoes i
             INNER JOIN empresas e ON e.id = i.empresa_id
             WHERE i.id = ?",
            [$licenca['instalacao_id']]
        );
    }
}


// ==========================================
// AÇÕES DA LICENÇA (NOVO MODELO)
// ==========================================
if (isset($_GET['suspender'])) {
    $id = (int) $_GET['suspender'];
    Database::execute("UPDATE licencas SET status = 'SUSPENSA' WHERE id = ?", [$id]);
    header('Location: licenca.php?id=' . $id . '&ok=suspenso');
    exit;
}

if (isset($_GET['reativar'])) {
    $id = (int) $_GET['reativar'];
    Database::execute("UPDATE licencas SET status = 'ATIVA' WHERE id = ?", [$id]);
    header('Location: licenca.php?id=' . $id . '&ok=reativado');
    exit;
}

if (isset($_GET['revogar'])) {
    $id = (int) $_GET['revogar'];
    Database::execute("UPDATE licencas SET status = 'REVOGADA' WHERE id = ?", [$id]);
    header('Location: licenca.php?id=' . $id . '&ok=revogado');
    exit;
}

if (isset($_GET['renovar'])) {
    $id = (int) $_GET['renovar'];
    $novaData = date('Y-m-d', strtotime('+1 year'));
    Database::execute("UPDATE licencas SET data_validade = ? WHERE id = ?", [$novaData, $id]);
    header('Location: licenca.php?id=' . $id . '&ok=renovado');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'instalacao_id'    => (int) $_POST['instalacao_id'],
        'tipo'             => $_POST['tipo'],
        'status'           => $_POST['status'],
        'data_ativacao'    => $_POST['data_ativacao'],
        'data_validade'    => $_POST['data_validade'],
        'ultima_validacao' => null
    ];

    if (!empty($_POST['id'])) {
        $controller->atualizar((int) $_POST['id'], $dados);
    } else {
        $controller->salvar($dados);
    }

    header('Location: licencas.php');
    exit;
}

$pageTitle = 'Licença';

require_once __DIR__ . '/includes/header.php';
?>

<main class="bt-main">

<section class="bt-card">
    <h2>Cadastro de Licença</h2>
    <p>Cadastro de licença da BT Queue Enterprise.</p>
    <hr style="margin:20px 0;">

    <form method="POST">
        <?php if ($licenca): ?>
            <input type="hidden" name="id" value="<?= $licenca['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Instalação</label>
            <select name="instalacao_id" class="form-control" required>
                <option value="">Selecione...</option>
                <?php foreach ($instalacoes as $i): ?>
                    <option value="<?= $i['id'] ?>" <?= (isset($licenca['instalacao_id']) && $licenca['instalacao_id'] == $i['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($i['empresa']) ?> - <?= htmlspecialchars($i['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin-top:15px;">
            <label>Tipo</label>
            <select name="tipo" class="form-control">
                <option value="ENTERPRISE" <?= ($licenca['tipo'] ?? '') === 'ENTERPRISE' ? 'selected' : '' ?>>🏢 Enterprise</option>
                <option value="PRO" <?= ($licenca['tipo'] ?? '') === 'PRO' ? 'selected' : '' ?>>🚀 PRO</option>
                <option value="PREMIUM" <?= ($licenca['tipo'] ?? '') === 'PREMIUM' ? 'selected' : '' ?>>⭐ Premium</option>
                <option value="DEMONSTRACAO" <?= ($licenca['tipo'] ?? '') === 'DEMONSTRACAO' ? 'selected' : '' ?>>🧪 Demonstração</option>
            </select>
        </div>

        <div class="form-group" style="margin-top:15px;">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="ATIVA" <?= ($licenca['status'] ?? '') === 'ATIVA' ? 'selected' : '' ?>>🟢 Ativa</option>
                <option value="SUSPENSA" <?= ($licenca['status'] ?? '') === 'SUSPENSA' ? 'selected' : '' ?>>🔴 Suspensa</option>
                <option value="REVOGADA" <?= ($licenca['status'] ?? '') === 'REVOGADA' ? 'selected' : '' ?>>⚫ Revogada</option>
                <option value="EXPIRADA" <?= ($licenca['status'] ?? '') === 'EXPIRADA' ? 'selected' : '' ?>>⏳ Expirada</option>
            </select>
        </div>

        <div class="form-group" style="margin-top:15px;">
            <label>Data de Ativação</label>
            <input type="date" name="data_ativacao" class="form-control" value="<?= htmlspecialchars($licenca['data_ativacao'] ?? '') ?>">
        </div>

        <div class="form-group" style="margin-top:15px;">
            <label>Data de Validade</label>
            <input type="date" name="data_validade" class="form-control" value="<?= htmlspecialchars($licenca['data_validade'] ?? '') ?>" required>
        </div>

        <button type="submit" class="bt-button bt-primary" style="margin-top:25px;">
            Salvar Licença
        </button>
        <a href="licencas.php" class="bt-button" style="margin-left:10px;">Voltar</a>
    </form>
</section>

<?php if ($licenca && $instalacao): ?>
    <!-- ========================================== -->
    <!-- QR CODE DE ATIVAÇÃO                        -->
    <!-- ========================================== -->
    <section class="bt-card" style="margin-top:20px;">
        <h3>📱 QR Code de Ativação</h3>
        <p style="color:#888; font-size:13px; margin-bottom:15px;">
            Escaneie este QR Code com o BT1 Coletor Pro para ativar o dispositivo.
        </p>

        <?php
        // Gerar QR Code
        $urlPlatform = 'http://api.brandaotech.com.br:8080/api/v1/sync.php';
        $dadosQR = json_encode([
            'protocol' => 1,
            'url' => $urlPlatform,
            'uuid' => $instalacao['uuid'],
            'token' => $instalacao['token']
        ]);

        $qrFile = "/tmp/qrcode_" . $instalacao['uuid'] . ".png";
        error_log("=== GERANDO QR CODE ===");
        error_log("Arquivo: " . $qrFile);
        error_log("Dados: " . $dadosQR);
        exec("/usr/bin/qrencode -o $qrFile '" . addslashes($dadosQR) . "' 2>&1", $output, $returnCode);
        error_log("Return Code: " . $returnCode);
        error_log("Output: " . implode(", ", $output));
        if (file_exists($qrFile)) {
            error_log("✅ QR Code gerado com sucesso!");
        } else {
            error_log("❌ QR Code NÃO foi gerado!");
        }
        
        if (file_exists($qrFile)) {
            $qrBase64 = base64_encode(file_get_contents($qrFile));
        } else {
            $qrBase64 = '';
        }
        ?>

        <div style="text-align:center; padding:20px; background:#fff; border-radius:12px;">
            <?php if ($qrBase64): ?>
                <img src="data:image/png;base64,<?= $qrBase64 ?>" 
                     alt="QR Code" 
                     style="width:200px; height:200px; image-rendering:pixelated;">
            <?php else: ?>
                <p style="color:#FF4D4D;">❌ Erro ao gerar QR Code. Verifique se o qrencode está instalado.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top:15px; background:#0d1b2a; padding:12px; border-radius:8px; font-size:12px; color:#888;">
            <strong style="color:#fff;">📋 Dados do QR Code</strong><br>
            Produto: <span style="color:#1DB4FF;"><?= htmlspecialchars($instalacao['produto'] ?? 'BT_QUEUE_ENTERPRISE') ?></span><br>
            URL: <span style="color:#fff; word-break:break-all;"><?= htmlspecialchars($urlPlatform) ?></span><br>
            UUID: <span style="color:#fff; font-size:11px;"><?= htmlspecialchars($instalacao['uuid']) ?></span><br>
            TOKEN: <span style="color:#fff; font-size:11px;"><?= substr($instalacao['token'], 0, 20) ?>...</span>
        </div>

        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
            <a href="data:image/png;base64,<?= $qrBase64 ?>" 
               download="qrcode_<?= $instalacao['uuid'] ?>.png" 
               class="bt-button bt-primary">
                📥 Baixar QR Code
            </a>
            <button onclick="location.reload()" class="bt-button">
                🔄 Atualizar
            </button>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- ESTADO ATUAL DA LICENÇA                    -->
    <!-- ========================================== -->
    <section class="bt-card" style="margin-top:20px;">
        <h2>Estado Atual</h2>
        <table class="bt-table" width="100%">
            <tr>
                <th width="220">Empresa</th>
                <td><?= htmlspecialchars($licenca['empresa']) ?></td>
            </tr>
            <tr>
                <th>Instalação</th>
                <td><?= htmlspecialchars($licenca['instalacao']) ?></td>
            </tr>
            <tr>
                <th>Produto</th>
                <td><span style="background:#132238; padding:2px 10px; border-radius:12px; font-size:12px; color:#1DB4FF;"><?= htmlspecialchars($instalacao['produto'] ?? 'BT_QUEUE_ENTERPRISE') ?></span></td>
            </tr>
            <tr>
                <th>Licença</th>
                <td>🔒 <?= htmlspecialchars($licenca['chave_licenca']) ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    <?php
                    switch ($licenca['status']) {
                        case 'ATIVA': echo '🟢 Ativa'; break;
                        case 'SUSPENSA': echo '🔴 Suspensa'; break;
                        case 'REVOGADA': echo '⚫ Revogada'; break;
                        case 'EXPIRADA': echo '⏳ Expirada'; break;
                        default: echo htmlspecialchars($licenca['status']);
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>Validade</th>
                <td><?= date('d/m/Y', strtotime($licenca['data_validade'])) ?></td>
            </tr>
        </table>
    </section>
<?php endif; ?>

</main>

<?php
// Buscar dispositivos da instalação
$id_instalacao = (int) ($licenca['instalacao_id'] ?? $_GET['id'] ?? 0);
$dispositivos = [];

if ($id_instalacao > 0) {
    $dispositivos = Database::fetchAll(
        "SELECT *
         FROM dispositivos
         WHERE instalacao_id = ?
         AND ativo = 1
         ORDER BY ultima_sincronizacao DESC",
        [$id_instalacao]
    );
}
?>

<?php if (!empty($dispositivos)): ?>
<section class="bt-card" style="margin-top:20px;">
    <h3>📱 Dispositivos Vinculados</h3>
    <p style="color:#888; font-size:13px; margin-bottom:15px;">
        <?= count($dispositivos) ?> dispositivo(s) conectado(s) a esta instalação.
    </p>
    <div style="display:flex; gap:15px; flex-wrap:wrap;">
        <?php foreach ($dispositivos as $disp): ?>
            <div style="background: #0d1b2a; border-radius: 8px; padding: 12px 16px; flex: 1; min-width: 200px; border-left: 3px solid <?= $disp['status'] === 'ONLINE' ? '#18C964' : '#666' ?>;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-weight:bold; color:#1DB4FF;"><?= htmlspecialchars($disp['modelo'] ?? 'Desconhecido') ?></span>
                    <span style="background: <?= $disp['status'] === 'ONLINE' ? '#18C964' : '#666' ?>; color: #fff; padding: 2px 10px; border-radius: 12px; font-size: 11px;">
                        <?= $disp['status'] ?? 'PENDENTE' ?>
                    </span>
                </div>
                <div style="color:#888; font-size:12px; margin-top:4px;">
                    UUID: <?= substr($disp['device_uuid'], 0, 12) ?>...
                </div>
                <div style="display:flex; gap:15px; color:#888; font-size:12px; margin-top:4px; flex-wrap:wrap;">
                    <span>📱 v<?= htmlspecialchars($disp['versao_app'] ?? '1.0.0') ?></span>
                    <?php if ($disp['bateria']): ?>
                        <span>🔋 <?= $disp['bateria'] ?>%</span>
                    <?php endif; ?>
                    <?php if ($disp['fabricante']): ?>
                        <span>🏭 <?= htmlspecialchars($disp['fabricante']) ?></span>
                    <?php endif; ?>
                    <?php if ($disp['ultima_sincronizacao']): ?>
                        <span>⏱️ <?= date('d/m/Y H:i', strtotime($disp['ultima_sincronizacao'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php
require_once __DIR__ . '/includes/footer.php';
