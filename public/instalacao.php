<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\App\Instalacao\InstalacaoController;
use BT\Core\Database\Database;

$controller = new InstalacaoController();

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
        'slug' => trim($_POST['slug'] ?? ''),
        'versao' => trim($_POST['versao'] ?? '4.0.0'),
        'status' => $_POST['status'] ?? 'ONLINE'
    ];
    
    $resultado = $controller->salvar($dados);
    
    if ($resultado) {
        header('Location: instalacao.php?ok=1');
        exit;
    } else {
        $erro = 'Erro ao salvar a instalação. Verifique os dados.';
    }
}

$pageTitle = 'Nova Instalação';

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

<option value="<?= $empresa['id'] ?>">
    <?= htmlspecialchars($empresa['nome_fantasia']) ?>
</option>

<?php endforeach; ?>

</select>

</div>

<div class="form-group" style="margin-top:15px;">

<label>Subdomínio (Exclusivo) *</label>

<div style="display:flex; align-items:center; gap:5px;">
    <input
        type="text"
        name="slug"
        class="form-control"
        required
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
    value="1.0.0"
    placeholder="1.0.0">

</div>

<div class="form-group" style="margin-top:15px;">

<label>Status</label>

<select
    name="status"
    class="form-control">

<option value="ONLINE">
    🟢 Online
</option>

<option value="OFFLINE" selected>
    🔴 Offline
</option>

<option value="MANUTENCAO">
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
require_once __DIR__ . '/includes/footer.php';
