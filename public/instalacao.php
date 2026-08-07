<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\App\Instalacao\InstalacaoController;
use BT\Core\Database\Database;

$controller = new InstalacaoController();

// --- AÇÃO: REMOVER DISPOSITIVO (DESATIVAR VAGA) ---
if (isset($_GET['remover_dispositivo'])) {
    $dispId = (int) $_GET['remover_dispositivo'];
    $instalacaoId = (int) ($_GET['id'] ?? 0);
    $controller->removerDispositivo($dispId);
    header("Location: instalacao.php?id=$instalacaoId&removido=1");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$instalacao = null;

if ($id > 0) {
    $instalacao = $controller->buscar($id);
    if (!$instalacao) {
        header('Location: instalacoes.php');
        exit;
    }
}

$empresas = Database::fetchAll(
    "SELECT
        id,
        nome_fantasia
     FROM empresas
     ORDER BY nome_fantasia"
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $dados = [
        'empresa_id' => (int) ($_POST['empresa_id'] ?? 0),
        'nome' => trim($_POST['nome'] ?? ''),
        'produto' => $_POST['produto'] ?? 'BT_QUEUE_ENTERPRISE',

        'versao' => trim($_POST['versao'] ?? '1.0.0'),
        'status' => $_POST['status'] ?? 'OFFLINE'
    ];
    
    $resultado = $controller->salvar($dados);
    
    if ($resultado) {
        header('Location: instalacao.php?ok=1');
        exit;
    } else {
        $erro = 'Erro ao salvar a instalação. Verifique os dados.';
    }
}

$pageTitle = $id > 0 ? 'Editar Instalação' : 'Nova Instalação';

require_once __DIR__ . '/includes/header.php';
?>

<main class="bt-main">

<section class="bt-card">

<h2>🖥️ Nova Instalação</h2>

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
<?php endif; ?>

<div class="form-group">
<label>Empresa *</label>
<select
    name="empresa_id"
    class="form-control"
    required>
<option value="">
Selecione...
</option>
<?php foreach ($empresas as $empresa): ?>
<option value="<?= $empresa['id'] ?>" <?= ($instalacao && $instalacao['empresa_id'] == $empresa['id']) ? 'selected' : '' ?>>
    <?= htmlspecialchars($empresa['nome_fantasia']) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="form-group" style="margin-top:15px;">
<label>Nome da Instalação *</label>
<input
    type="text"
    name="nome"
    class="form-control"
    required
    value="<?= $instalacao ? htmlspecialchars($instalacao['nome']) : '' ?>"
    placeholder="Ex: Produção, Homologação, Desenvolvimento">
</div>

<div class="form-group" style="margin-top:15px;">
    <label>Produto *</label>
    <select name="produto" class="form-control" required>
        <option value="BT_QUEUE_ENTERPRISE" <?= ($instalacao && $instalacao['produto'] === 'BT_QUEUE_ENTERPRISE') ? 'selected' : '' ?>>
            🎫 BT Queue Enterprise
        </option>
        <option value="BT1_COLETOR_PRO" <?= ($instalacao && $instalacao['produto'] === 'BT1_COLETOR_PRO') ? 'selected' : '' ?>>
            📱 BT1 Coletor Pro
        </option>
        <option value="BT_PRINT" <?= ($instalacao && $instalacao['produto'] === 'BT_PRINT') ? 'selected' : '' ?>>
            🖨️ BT Print Server
        </option>
    </select>
</div>



<div class="form-group" style="margin-top:15px;">

<label>Versão Enterprise</label>

<input
    type="text"
    name="versao"
    class="form-control"
    value="<?= $instalacao ? htmlspecialchars($instalacao['versao']) : '1.0.0' ?>"
    placeholder="1.0.0">

</div>




<div class="form-group" style="margin-top:15px;">
<label>Status</label>
<select
    name="status"
    class="form-control">
<option value="ONLINE" <?= ($instalacao && $instalacao['status'] === 'ONLINE') ? 'selected' : '' ?>>
    🟢 Online
</option>
<option value="OFFLINE" <?= (!$instalacao || $instalacao['status'] === 'OFFLINE') ? 'selected' : '' ?>>
    🔴 Offline
</option>
<option value="MANUTENCAO" <?= ($instalacao && $instalacao['status'] === 'MANUTENCAO') ? 'selected' : '' ?>>
    🟡 Manutenção
</option>
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

<section class="bt-card" style="margin-top:20px;">

<h3>📋 Informações</h3>

<ul style="padding-left:20px;">
    <li><strong>Empresa:</strong> Selecione a empresa proprietária da instalação</li>
    <li><strong>Nome:</strong> Dê um nome descritivo para a instalação</li>
    <li><strong>UUID:</strong> Gerado automaticamente pela Platform.</li>
    <li><strong>Token:</strong> Gerado automaticamente pela Platform.</li>
    <li><strong>Versão:</strong> Versão atual do BT Queue Enterprise</li>
    <li><strong>Status:</strong> Situação atual da instalação</li>
</ul>

</section>

</main>

<?php 
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0): 
    $masterUrl = "http://api.brandaotech.com.br:8080/api/v1/sync.php";
    $magicString = $masterUrl . "|" . $instalacao['uuid'] . "|" . $instalacao['token'];
?>
<section class="bt-card" style="margin-top:20px; border-left: 4px solid var(--secondary);">
    <h3 style="color: var(--secondary);"><i class="fa-solid fa-bolt"></i> Carga Rápida de Provisionamento</h3>
    <p style="font-size: 13px; color: var(--text2);">Copie o bloco abaixo e cole no campo "CARGA RÁPIDA" do instalador no cliente.</p>

    <div style="background: var(--sidebar); padding: 15px; border-radius: 8px; margin: 15px 0; border: 1px solid var(--border);">
        <code id="magicString" style="color: var(--success); word-break: break-all; font-size: 12px;"><?= $magicString ?></code>
    </div>

    <button onclick="copyMagic()" class="bt-button" style="background: var(--secondary); color: #fff; font-size: 12px; padding: 8px 15px;">
        <i class="fa-solid fa-copy"></i> Copiar Bloco de Ativação
    </button>

    <script>
        function copyMagic() {
            const text = document.getElementById('magicString').innerText;
            navigator.clipboard.writeText(text);
            alert("Bloco de ativação copiado!");
        }
    </script>
</section>

<section class="bt-card" style="margin-top:20px;">
    <h3>📱 Dispositivos Vinculados</h3>

    <?php if (isset($_GET['removido'])): ?>
        <div class="bt-alert bt-success" style="margin-bottom: 15px;">
            ✅ Dispositivo removido da licença com sucesso!
        </div>
    <?php endif; ?>

    <?php
    $dispositivos = $controller->buscarDispositivos($id);
    ?>

    <?php if (empty($dispositivos)): ?>
        <p style="color:#888;">Nenhum dispositivo vinculado a esta instalação.</p>
    <?php else: ?>
        <table class="bt-table" width="100%">
            <thead>
                <tr>
                    <th>Dispositivo</th>
                    <th>Modelo</th>
                    <th>Fabricante</th>
                    <th>Versão</th>
                    <th>Status</th>
                    <th>Última Sinc.</th>
                    <th>Bateria</th>
                    <th align="right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dispositivos as $disp): ?>
                    <tr>
                        <td style="font-size:12px;"><?= substr($disp['device_uuid'], 0, 12) ?>...</td>
                        <td><?= htmlspecialchars($disp['modelo'] ?? 'Desconhecido') ?></td>
                        <td><?= htmlspecialchars($disp['fabricante'] ?? 'Desconhecido') ?></td>
                        <td><?= htmlspecialchars($disp['versao_app'] ?? '1.0.0') ?></td>
                        <td>
                            <?php
                                $isRealOnline = false;
                                if ($disp['ultima_sincronizacao'] ?? null) {
                                    $isRealOnline = (time() - strtotime($disp['ultima_sincronizacao'])) < 1800;
                                }
                            ?>
                            <span style="background: <?= $isRealOnline ? '#18C964' : '#FF4D4D' ?>; color: #fff; padding: 2px 10px; border-radius: 12px; font-size: 11px;">
                                <?= $isRealOnline ? 'ONLINE' : 'OFFLINE' ?>
                            </span>
                        </td>
                        <td><?= $disp['ultima_sincronizacao'] ?? 'Nunca' ?></td>
                        <td><?php if ($disp['bateria']): ?>🔋 <?= $disp['bateria'] ?>%<?php else: ?>--<?php endif; ?></td>
                        <td align="right">
                            <button
                                onclick="removerDispositivo(<?= $disp['id'] ?>, '<?= htmlspecialchars($disp['modelo'] ?? 'Desconhecido') ?>')"
                                class="bt-button"
                                style="background:#ff4d4d; padding: 5px 10px; color:#fff;"
                                title="Remover Dispositivo">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<script>
    function removerDispositivo(id, modelo) {
        if (confirm('Deseja realmente remover o dispositivo "' + modelo + '" desta licença? \n\nIsso liberará uma vaga na assinatura do cliente.')) {
            const instalacaoId = '<?= $id ?>';
            window.location.href = 'instalacao.php?id=' + instalacaoId + '&remover_dispositivo=' + id;
        }
    }
</script>
<?php endif; ?>
<?php
require_once __DIR__ . '/includes/footer.php';
