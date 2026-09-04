<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';
require_once __DIR__ . '/../bootstrap/autoload.php';

use BT\App\Planos\PlanosController;

$controller = new PlanosController();

// Ações (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'id' => $_POST['id'] ? (int)$_POST['id'] : null,
        'nome' => $_POST['nome'],
        'slug' => $_POST['slug'],
        'preco_mensal' => $_float = (float)str_replace(',', '.', $_POST['preco_mensal']),
        'taxa_setup' => (float)str_replace(',', '.', $_POST['taxa_setup']),
        'max_dispositivos' => (int)$_POST['max_dispositivos'],
        'recursos' => $_POST['recursos'],
        'status' => $_POST['status']
    ];
    $controller->salvar($dados);
    header('Location: planos_gerenciar.php?msg=Salvo com sucesso!');
    exit;
}

// Exclusão
if (isset($_GET['delete'])) {
    $controller->excluir((int)$_GET['delete']);
    header('Location: planos_gerenciar.php?msg=Removido!');
    exit;
}

$planos = $controller->listar();
$pageTitle = 'Gerenciar Planos SaaS';
require_once __DIR__ . '/includes/header.php';
?>

<main class="bt-main">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2>⚙️ Configurar Ofertas Comerciais</h2>
        <button onclick="abrirModal()" class="bt-button bt-primary">➕ NOVA OFERTA</button>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div style="background:var(--success); color:#fff; padding:15px; border-radius:10px; margin-bottom:20px;"><?= $_GET['msg'] ?></div>
    <?php endif; ?>

    <div class="bt-card">
        <table class="status-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Mensalidade</th>
                    <th>Setup</th>
                    <th>Disp.</th>
                    <th>Status</th>
                    <th style="text-align:right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($planos as $p): ?>
                <tr>
                    <td><b><?= $p['nome'] ?></b><br><small style="color:var(--text3)"><?= $p['slug'] ?></small></td>
                    <td>R$ <?= number_format((float)$p['preco_mensal'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format((float)$p['taxa_setup'], 2, ',', '.') ?></td>
                    <td><?= $p['max_dispositivos'] ?></td>
                    <td>
                        <span class="badge <?= $p['status'] === 'ATIVO' ? 'success' : 'warning' ?>"><?= $p['status'] ?></span>
                    </td>
                    <td align="right">
                        <button onclick='editarPlan(<?= json_encode($p) ?>)' class="bt-button" style="padding:5px 10px; font-size:11px;">✏️ EDITAR</button>
                        <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Excluir este plano?')" class="bt-button" style="background:var(--danger); padding:5px 10px; font-size:11px; text-decoration:none;">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- MODAL DE PLANO -->
<div id="modalPlano" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--card); width:500px; padding:30px; border-radius:20px; border:1px solid var(--border);">
        <h3 id="modalTitle" style="margin-bottom:25px;">Novo Plano</h3>
        <form method="POST">
            <input type="hidden" name="id" id="plano_id">

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>Nome do Plano</label>
                    <input type="text" name="nome" id="plano_nome" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Identificador (Slug)</label>
                    <input type="text" name="slug" id="plano_slug" class="form-control" placeholder="ex: basic" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top:15px;">
                <div class="form-group">
                    <label>Preço Mensal (R$)</label>
                    <input type="text" name="preco_mensal" id="plano_preco" class="form-control" placeholder="150,00" required>
                </div>
                <div class="form-group">
                    <label>Taxa de Setup (R$)</label>
                    <input type="text" name="taxa_setup" id="plano_setup" class="form-control" placeholder="297,00" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top:15px;">
                <div class="form-group">
                    <label>Max Dispositivos</label>
                    <input type="number" name="max_dispositivos" id="plano_disp" class="form-control" value="1" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="plano_status" class="form-control">
                        <option value="ATIVO">ATIVO</option>
                        <option value="INATIVO">INATIVO</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label>Recursos (Separados por vírgula)</label>
                <textarea name="recursos" id="plano_recursos" class="form-control" style="height:100px;"></textarea>
            </div>

            <div style="display:flex; gap:10px; margin-top:30px;">
                <button type="button" onclick="fecharModal()" class="bt-button" style="background:var(--sidebar); border:1px solid var(--border);">CANCELAR</button>
                <button type="submit" class="bt-button bt-primary">SALVAR PLANO</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal() {
    document.getElementById('modalTitle').innerText = 'Novo Plano';
    document.getElementById('plano_id').value = '';
    document.getElementById('plano_nome').value = '';
    document.getElementById('plano_slug').value = '';
    document.getElementById('plano_preco').value = '';
    document.getElementById('plano_setup').value = '';
    document.getElementById('plano_disp').value = '1';
    document.getElementById('plano_recursos').value = '';
    document.getElementById('modalPlano').style.display = 'flex';
}

function fecharModal() {
    document.getElementById('modalPlano').style.display = 'none';
}

function editarPlan(p) {
    document.getElementById('modalTitle').innerText = 'Editar Plano';
    document.getElementById('plano_id').value = p.id;
    document.getElementById('plano_nome').value = p.nome;
    document.getElementById('plano_slug').value = p.slug;
    document.getElementById('plano_preco').value = p.preco_mensal;
    document.getElementById('plano_setup').value = p.taxa_setup;
    document.getElementById('plano_disp').value = p.max_dispositivos;
    document.getElementById('plano_recursos').value = p.recursos;
    document.getElementById('plano_status').value = p.status;
    document.getElementById('modalPlano').style.display = 'flex';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
