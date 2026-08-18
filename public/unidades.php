<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/auth.php';

use BT\Core\Database\Database;

$pageTitle = 'Unidades SaaS';

$unidades = Database::fetchAll("
    SELECT
        i.id,
        e.nome_fantasia as empresa,
        i.nome as unidade,
        i.uuid,
        i.status,
        i.versao,
        l.status as status_licenca
    FROM instalacoes i
    LEFT JOIN empresas e ON e.id = i.empresa_id
    LEFT JOIN licencas l ON l.instalacao_id = i.id
    ORDER BY e.nome_fantasia, i.nome
");

require_once __DIR__ . '/includes/header.php';
?>

<main class="bt-main">
    <section class="bt-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h2>🚀 Unidades SaaS (Multi-Tenant)</h2>
                <p>Gerencie todas as barbearias conectadas ao seu ecossistema.</p>
            </div>
            <a href="instalacao.php" class="bt-button bt-primary">
                ➕ Ativar Nova Unidade
            </a>
        </div>

        <table class="bt-table" width="100%">
            <thead>
                <tr>
                    <th>Empresa / Barbearia</th>
                    <th>Subdomínio (Slug)</th>
                    <th>Estado</th>
                    <th>Licença</th>
                    <th>Versão</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unidades as $u): ?>
                <tr>
                    <td>
                        <b><?= htmlspecialchars($u['empresa']) ?></b><br>
                        <small style="color:var(--text2)"><?= htmlspecialchars($u['unidade']) ?></small>
                    </td>
                    <td>
                        <code style="color:var(--secondary)"><?= strtolower(str_replace(' ', '', $u['unidade'])) ?>.brandaotech.com.br</code>
                    </td>
                    <td>
                        <span class="badge <?= $u['status'] === 'ONLINE' ? 'badge-success' : 'badge-danger' ?>">
                            <?= $u['status'] ?>
                        </span>
                    </td>
                    <td>
                        <span style="color: <?= $u['status_licenca'] === 'ATIVA' ? 'var(--success)' : 'var(--danger)' ?>">
                            ● <?= $u['status_licenca'] ?>
                        </span>
                    </td>
                    <td>v<?= $u['versao'] ?></td>
                    <td>
                        <a href="instalacao.php?id=<?= $u['id'] ?>" class="bt-button">⚙️ Gerenciar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<?php
require_once __DIR__ . '/includes/footer.php';
