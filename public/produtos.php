<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\Core\Database\Database;

$pageTitle = 'Gestão de Produtos Diamond';
require_once __DIR__ . '/includes/header.php';

// AÇÃO: CADASTRAR/EDITAR PRODUTO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $slug = strtoupper(str_replace(' ', '_', $nome));

    Database::execute(
        "REPLACE INTO produtos (id, nome, slug, icone, versao_estavel) VALUES (?, ?, ?, ?, ?)",
        [$_POST['id'] ?: null, $nome, $slug, $_POST['icone'], $_POST['versao']]
    );
    $msg = "Produto salvo com sucesso!";
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

// Garante que a coluna ip_local existe na tabela instalacoes (Auto-Cura da Auditoria)
try {
    Database::execute("ALTER TABLE instalacoes ADD COLUMN ip_local VARCHAR(50) NULL AFTER ultimo_ip");
} catch (Exception $e) {
    // Coluna já existe ou erro ignorável
}

// Cadastra os produtos base se não existirem
$baseProducts = [
    ['🎫 BT Queue Enterprise', 'BT_QUEUE_ENTERPRISE', 'fa-ticket', '7.4.0'],
    ['📊 BT Integration Platform', 'BT_INTEGRATION_PLATFORM', 'fa-chart-line', '1.2.0'],
    ['📱 BT1 Coletor Pro', 'BT1_COLETOR_PRO', 'fa-mobile-screen', '1.8.0'],
    ['🖨️ BT Print Server', 'BT_PRINT', 'fa-print', '1.0.0']
];

foreach ($baseProducts as $p) {
    Database::execute("INSERT IGNORE INTO produtos (nome, slug, icone, versao_estavel) VALUES (?, ?, ?, ?)", $p);
}

$produtos = Database::fetchAll("SELECT * FROM produtos ORDER BY nome ASC");
?>

<style>
    .prod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 30px; }
    .prod-card { background: var(--card); border: 1px solid var(--border); border-radius: 15px; padding: 25px; transition: 0.3s; }
    .prod-card:hover { border-color: var(--secondary); transform: translateY(-5px); }
    .prod-icon { font-size: 30px; color: var(--secondary); margin-bottom: 15px; }
    .prod-title { font-size: 16px; font-weight: 800; margin-bottom: 5px; }
    .prod-slug { font-size: 10px; color: var(--text3); text-transform: uppercase; font-family: monospace; }
    .prod-ver { margin-top: 15px; display: flex; justify-content: space-between; align-items: center; }
</style>

<main class="bt-main">

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="margin:0;">📦 Catálogo de Produtos Diamond</h2>
            <p style="color:var(--text2); font-size:14px;">Gerencie as versões e identidades dos softwares Brandão Tech.</p>
        </div>
        <button class="bt-button bt-primary" onclick="alert('Funcionalidade de novo produto em breve.')">
            ➕ Novo Produto
        </button>
    </div>

    <?php if(isset($msg)) echo "<div class='bt-alert bt-success' style='margin-top:20px;'>✅ $msg</div>"; ?>

    <div class="prod-grid">
        <?php foreach ($produtos as $p): ?>
        <div class="prod-card">
            <div class="prod-icon"><i class="fa-solid <?= $p['icone'] ?>"></i></div>
            <div class="prod-title"><?= htmlspecialchars($p['nome']) ?></div>
            <div class="prod-slug"><?= $p['slug'] ?></div>

            <div class="prod-ver">
                <span style="font-size:12px; color:var(--text2);">Estável: <b style="color:#fff"><?= $p['versao_estavel'] ?></b></span>
                <span class="badge" style="background:rgba(29, 180, 255, 0.1); color:var(--secondary); font-size:10px;">PROATIVO</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <section class="bt-card" style="margin-top:40px; border-left: 4px solid var(--warning);">
        <h4 style="color:var(--warning);"><i class="fa-solid fa-circle-info"></i> Informativo de Engenharia</h4>
        <p style="font-size:13px; color:var(--text2); line-height:1.6; margin-top:10px;">
            Este catálogo define as assinaturas digitais aceitas pelo motor de licenciamento.
            Ao cadastrar um novo produto aqui, o motor <b>MasterSync</b> passa a reconhecer as conexões vindas do novo Appliance automaticamente.
        </p>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
