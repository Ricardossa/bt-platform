<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\Core\Database\Database;

$pageTitle = 'Gestão de Frota por Empresa';

// ==========================================
// EXCLUIR EMPRESA (COM VERIFICAÇÃO)
// ==========================================
if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];
    
    // 1. Remove dispositivos das instalações da empresa
    Database::execute(
        "DELETE d FROM dispositivos d
         INNER JOIN instalacoes i ON i.id = d.instalacao_id
         WHERE i.empresa_id = ?",
        [$id]
    );
    
    // 2. Remove licenças das instalações da empresa
    Database::execute(
        "DELETE l FROM licencas l
         INNER JOIN instalacoes i ON i.id = l.instalacao_id
         WHERE i.empresa_id = ?",
        [$id]
    );
    
    // 3. Remove instalações da empresa
    Database::execute(
        "DELETE FROM instalacoes WHERE empresa_id = ?",
        [$id]
    );
    
    // 4. Remove a empresa
    Database::execute(
        "DELETE FROM empresas WHERE id = ?",
        [$id]
    );
    
    header('Location: empresas.php?excluido=1');
    exit;
}

$empresas = Database::fetchAll("
    SELECT
        e.*,
        COUNT(i.id) as total_instalacoes,
        (SELECT COUNT(*) FROM dispositivos d WHERE d.instalacao_id IN (SELECT id FROM instalacoes WHERE empresa_id = e.id) AND d.ativo = 1) as total_dispositivos,
        (SELECT GROUP_CONCAT(DISTINCT produto) FROM instalacoes WHERE empresa_id = e.id) as produtos
    FROM empresas e
    LEFT JOIN instalacoes i ON i.empresa_id = e.id
    GROUP BY e.id
    ORDER BY e.nome_fantasia
");

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .fleet-card { background: var(--sidebar); border: 1px solid var(--border); border-radius: 12px; padding: 20px; transition: .2s; }
    .fleet-card:hover { border-color: var(--primary); transform: translateY(-3px); box-shadow: var(--shadow); }
    .fleet-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 25px; }
    .product-tag { font-size: 10px; background: rgba(29, 180, 255, 0.1); color: var(--secondary); padding: 2px 8px; border-radius: 4px; font-weight: bold; }
    .contact-link { color: var(--text2); text-decoration: none; font-size: 13px; display: flex; align-items: center; gap: 8px; margin-top: 5px; }
    .contact-link:hover { color: var(--secondary); }
    .device-count-badge { background: var(--success); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; }
</style>

<main class="bt-main">

<?php if (isset($_GET['excluido'])): ?>
    <div class="bt-alert bt-success">
        ✅ Empresa excluída com sucesso!
    </div>
<?php endif; ?>

    <section class="bt-card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h2 style="margin:0;">🏢 Gestão de Clientes (Frota)</h2>
                <p style="color:var(--text2); font-size:14px; margin-top:5px;">Acompanhamento de ativos e contatos por empresa cadastrada.</p>
            </div>
            <a href="empresa.php" class="bt-button bt-primary">
                ➕ Nova Empresa
            </a>
        </div>

        <div class="fleet-grid">
            <?php foreach ($empresas as $e): ?>
            <div class="fleet-card">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                    <div>
                        <div style="font-size:18px; font-weight:bold; color:var(--text);"><?= htmlspecialchars($e['nome_fantasia']) ?></div>
                        <div style="font-size:12px; color:var(--text2);"><?= htmlspecialchars($e['cnpj'] ?: 'Sem CNPJ') ?></div>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:5px;">
                        <span class="device-count-badge"><?= $e['total_dispositivos'] ?></span>
                        <span style="font-size:10px; color:var(--text2); text-transform:uppercase;">Dispositivos</span>
                    </div>
                </div>

                <div style="display:flex; gap:8px; margin-bottom:15px; flex-wrap:wrap;">
                    <?php
                        $prods = array_unique(explode(',', $e['produtos'] ?? ''));
                        foreach($prods as $p) {
                            if($p) echo "<span class='product-tag'>$p</span>";
                        }
                    ?>
                </div>

                <div style="padding-top:15px; border-top:1px solid rgba(255,255,255,0.05);">
                    <?php if($e['whatsapp']): ?>
                        <a href="https://wa.me/<?= preg_replace('/\D/', '', $e['whatsapp']) ?>" target="_blank" class="contact-link">
                            <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($e['whatsapp']) ?>
                        </a>
                    <?php endif; ?>
                    <?php if($e['email']): ?>
                        <div class="contact-link"><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($e['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div style="margin-top:20px; display:flex; gap:10px;">
                    <a href="empresa.php?id=<?= $e['id'] ?>" class="bt-button" style="flex:1; padding:10px; font-size:13px; text-decoration:none; text-align:center;">Gerenciar</a>
                    <a href="empresas.php?excluir=<?= $e['id'] ?>" class="bt-button" style="padding:10px; font-size:13px; color:var(--danger);" onclick="return confirm('Excluir empresa?')"><i class="fa-solid fa-trash"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
