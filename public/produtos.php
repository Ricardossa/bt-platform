<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\Core\Database\Database;

$pageTitle = 'Gestão de Produtos Diamond';
require_once __DIR__ . '/includes/header.php';

// AÇÃO: EXCLUIR PRODUTO
if (isset($_GET['delete'])) {
    Database::execute("DELETE FROM produtos WHERE id = ?", [(int)$_GET['delete']]);
    header('Location: produtos.php?ok=deleted');
    exit;
}

// AÇÃO: CADASTRAR/EDITAR PRODUTO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $nome = trim($_POST['nome']);
    $slug = strtoupper(trim($_POST['slug']));
    $icone = trim($_POST['icone'] ?: 'fa-cube');
    $versao = trim($_POST['versao'] ?: '1.0.0');

    if ($id) {
        Database::execute(
            "UPDATE produtos SET nome = ?, slug = ?, icone = ?, versao_estavel = ? WHERE id = ?",
            [$nome, $slug, $icone, $versao, $id]
        );
    } else {
        Database::execute(
            "INSERT INTO produtos (nome, slug, icone, versao_estavel) VALUES (?, ?, ?, ?)",
            [$nome, $slug, $icone, $versao]
        );
    }
    header('Location: produtos.php?ok=saved');
    exit;
}

// Garante que a tabela de produtos existe (Ajustado para MariaDB)
Database::execute("
    CREATE TABLE IF NOT EXISTS produtos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(255) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        icone VARCHAR(50),
        versao_estavel VARCHAR(20) DEFAULT '1.0.0',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

// Cadastra os produtos base se não existirem
$baseProducts = [
    ['🎫 BT Queue Enterprise', 'BT_QUEUE_ENTERPRISE', 'fa-ticket', '7.4.0'],
    ['💎 BT Enterprise Lite', 'BT_QUEUE_ENTERPRISE_LITE', 'fa-gem', '1.2.0'],
    ['📊 BT Integration Platform', 'BT_INTEGRATION_PLATFORM', 'fa-chart-line', '1.2.0'],
    ['📱 BT1 Coletor Pro', 'BT1_COLETOR_PRO', 'fa-mobile-screen', '1.8.0'],
    ['🖨️ BT Print Server', 'BT_PRINT', 'fa-print', '1.0.0']
];

foreach ($baseProducts as $p) {
    Database::execute("INSERT IGNORE INTO produtos (nome, slug, icone, versao_estavel) VALUES (?, ?, ?, ?)", $p);
}

// Busca produtos com métricas reais (Instalações vs Dispositivos)
$produtos = Database::fetchAll("
    SELECT p.*,
    (SELECT COUNT(DISTINCT i.id) FROM instalacoes i WHERE i.produto = p.slug) as total_instalacoes,
    (SELECT COUNT(DISTINCT d.id) FROM dispositivos d JOIN instalacoes i ON i.id = d.instalacao_id WHERE i.produto = p.slug AND d.ativo = 1) as total_dispositivos
    FROM produtos p
    ORDER BY p.nome ASC
");
?>

<style>
    .prod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 30px; }
    .prod-card { background: var(--card); border: 1px solid var(--border); border-radius: 15px; padding: 25px; transition: 0.3s; position: relative; }
    .prod-card:hover { border-color: var(--secondary); transform: translateY(-5px); }
    .prod-icon { font-size: 30px; color: var(--secondary); margin-bottom: 15px; }
    .prod-title { font-size: 16px; font-weight: 800; margin-bottom: 5px; color: #fff; }
    .prod-slug { font-size: 10px; color: var(--text3); text-transform: uppercase; font-family: monospace; }
    .prod-ver { margin-top: 15px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 15px; }
    .prod-actions { position: absolute; top: 15px; right: 15px; display: flex; gap: 8px; opacity: 0; transition: 0.2s; }
    .prod-card:hover .prod-actions { opacity: 1; }
    .action-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--text2); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
    .action-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
    .btn-delete:hover { background: var(--danger); border-color: var(--danger); }
    .stat-box { display: flex; gap: 20px; margin-top: 20px; }
    .stat-item { flex: 1; }
    .stat-label { font-size: 10px; color: var(--text3); text-transform: uppercase; margin-bottom: 5px; }
    .stat-value { font-size: 22px; font-weight: 900; }
</style>

<main class="bt-main">

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="margin:0;">📦 Catálogo de Produtos Diamond</h2>
            <p style="color:var(--text2); font-size:14px;">Gerencie as versões e identidades dos softwares Brandão Tech.</p>
        </div>
        <button class="bt-button bt-primary" onclick="abrirModalProduto()">
            ➕ Novo Produto
        </button>
    </div>

    <?php if(isset($_GET['ok'])): ?>
        <div class='bt-alert bt-success' style='margin-top:20px;'>
            ✅ <?= $_GET['ok'] === 'saved' ? 'Produto salvo com sucesso!' : 'Produto removido!' ?>
        </div>
    <?php endif; ?>

    <div class="prod-grid">
        <?php foreach ($produtos as $p): ?>
        <div class="prod-card">
            <div class="prod-actions">
                <button class="action-btn" onclick='editarProduto(<?= json_encode($p) ?>)' title="Editar"><i class="fa-solid fa-pen"></i></button>
                <a href="?delete=<?= $p['id'] ?>" class="action-btn btn-delete" onclick="return confirm('Excluir este produto?')" title="Excluir"><i class="fa-solid fa-trash"></i></a>
            </div>

            <div class="prod-icon"><i class="fa-solid <?= $p['icone'] ?>"></i></div>
            <div class="prod-title"><?= htmlspecialchars($p['nome']) ?></div>
            <div class="prod-slug"><?= $p['slug'] ?></div>

            <div class="stat-box">
                <div class="stat-item">
                    <div class="stat-label">Unidades</div>
                    <div class="stat-value" style="color: var(--secondary);"><?= $p['total_instalacoes'] ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Dispositivos</div>
                    <div class="stat-value" style="color: var(--success);"><?= $p['total_dispositivos'] ?></div>
                </div>
            </div>

            <div class="prod-ver">
                <span style="font-size:12px; color:var(--text2);">Estável: <b style="color:#fff"><?= $p['versao_estavel'] ?></b></span>
                <span class="badge" style="background:rgba(24, 201, 100, 0.1); color:var(--success); font-size:10px;">PROATIVO</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- MODAL: CADASTRAR/EDITAR PRODUTO -->
    <div id="modalProduto" class="bt-modal hidden" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:10000; align-items:center; justify-content:center;">
        <div class="bt-card" style="max-width:450px; width:90%; padding:30px;">
            <h3 id="modalTitle">Novo Produto</h3>
            <hr style="margin:15px 0; border:0; border-top:1px solid var(--border);">

            <form method="POST" id="formProduto">
                <input type="hidden" name="id" id="prod_id">

                <div class="form-group">
                    <label>Nome do Produto</label>
                    <input type="text" name="nome" id="prod_nome" class="form-control" required placeholder="Ex: BT Enterprise Lite">
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label>Identificador (SLUG)</label>
                    <input type="text" name="slug" id="prod_slug" class="form-control" required placeholder="Ex: BT_QUEUE_ENTERPRISE_LITE">
                    <small style="color:var(--text3); font-size:11px;">Deve ser igual ao que o software envia no MasterSync.</small>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top:15px;">
                    <div class="form-group">
                        <label>Ícone FontAwesome</label>
                        <input type="text" name="icone" id="prod_icone" class="form-control" placeholder="fa-cube">
                    </div>
                    <div class="form-group">
                        <label>Versão Estável</label>
                        <input type="text" name="versao" id="prod_versao" class="form-control" placeholder="1.0.0">
                    </div>
                </div>

                <div style="margin-top:30px; display:flex; gap:10px;">
                    <button type="submit" class="bt-button bt-primary" style="flex:2;">SALVAR PRODUTO</button>
                    <button type="button" class="bt-button" onclick="fecharModal()" style="background:#444; flex:1;">CANCELAR</button>
                </div>
            </form>
        </div>
    </div>

    <section class="bt-card" style="margin-top:40px; border-left: 4px solid var(--warning);">
        <h4 style="color:var(--warning);"><i class="fa-solid fa-circle-info"></i> Informativo de Engenharia</h4>
        <p style="font-size:13px; color:var(--text2); line-height:1.6; margin-top:10px;">
            Este catálogo define as assinaturas digitais aceitas pelo motor de licenciamento.
            Ao cadastrar um novo produto aqui, o motor <b>MasterSync</b> passa a reconhecer as conexões vindas do novo Appliance automaticamente.
        </p>
    </section>

</main>

<script>
    function abrirModalProduto() {
        document.getElementById('formProduto').reset();
        document.getElementById('prod_id').value = '';
        document.getElementById('modalTitle').innerText = 'Novo Produto';
        document.getElementById('modalProduto').style.display = 'flex';
    }

    function fecharModal() {
        document.getElementById('modalProduto').style.display = 'none';
    }

    function editarProduto(p) {
        document.getElementById('prod_id').value = p.id;
        document.getElementById('prod_nome').value = p.nome;
        document.getElementById('prod_slug').value = p.slug;
        document.getElementById('prod_icone').value = p.icone;
        document.getElementById('prod_versao').value = p.versao_estavel;
        document.getElementById('modalTitle').innerText = 'Editar Produto';
        document.getElementById('modalProduto').style.display = 'flex';
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
