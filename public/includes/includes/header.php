<?php
declare(strict_types=1);
require_once __DIR__ . '/platform_brand.php';
$pageTitle = $pageTitle ?? $empresa;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="stylesheet"
      href="/assets/css/theme.css">
<link rel="stylesheet"
      href="/assets/css/layout.css">
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
    /* ==========================================
       MENU - APLICATIVOS
       ========================================== */
    .menu-group {
        margin-top: 8px;
        margin-bottom: 4px;
        font-size: 11px;
        color: #6b7a8f;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 8px 16px 4px 16px;
        font-weight: bold;
    }
    .menu-sub {
        padding-left: 8px;
        border-left: 2px solid #1E3552;
        margin-left: 16px;
    }
    .menu-sub a {
        font-size: 13px;
        padding: 6px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #9ca3af;
        text-decoration: none;
        border-radius: 6px;
        transition: 0.2s;
    }
    .menu-sub a:hover {
        background: #1E3552;
        color: #fff;
    }
    .menu-sub a i {
        width: 18px;
        font-size: 14px;
        color: #1DB4FF;
    }
    .menu-sub a .badge {
        background: #18C964;
        color: #fff;
        font-size: 9px;
        padding: 1px 8px;
        border-radius: 10px;
        margin-left: auto;
    }
</style>
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <?php if ($logoExiste): ?>
            <img
                src="<?= htmlspecialchars($logoUrl) ?>"
                alt="<?= htmlspecialchars($empresa) ?>"
                style="
                    width:60px;
                    height:60px;
                    object-fit:contain;
                ">
        <?php else: ?>
            <div class="logo-icon">
                BT
            </div>
        <?php endif; ?>
        <div class="logo-text">
            <h2>
                <?= htmlspecialchars($empresa) ?>
            </h2>
            <span>
                Platform
            </span>
        </div>
    </div>
    <nav>
        <a href="index.php">
            <i class="fa-solid fa-house"></i>
            Dashboard
        </a>
        <a href="platform.php">
            <i class="fa-solid fa-gear"></i>
            Platform
        </a>
        <a href="empresas.php">
            <i class="fa-solid fa-building"></i>
            Empresas
        </a>
        <a href="instalacoes.php">
            <i class="fa-solid fa-server"></i>
            Instalações
        </a>
        <a href="licencas.php">
            <i class="fa-solid fa-key"></i>
            Licenças
        </a>

        <!-- ========================================== -->
        <!-- APLICATIVOS                                -->
        <!-- ========================================== -->
        <div class="menu-group">📦 Aplicativos</div>
        <div class="menu-sub">
            <a href="instalacoes.php?produto=BT_QUEUE_ENTERPRISE">
                <i class="fa-solid fa-ticket"></i>
                BT Queue Enterprise
                <span class="badge">v2.0</span>
            </a>
            <a href="instalacoes.php?produto=BT1_COLETOR_PRO">
                <i class="fa-solid fa-mobile-screen-button"></i>
                BT1 Coletor Pro
                <span class="badge">v1.0</span>
            </a>
        </div>
    </nav>
</div>
<div class="content">
<header class="topbar">
<h1>
    <?= htmlspecialchars($pageTitle) ?>
</h1>
<div class="status">
    <span class="online"></span>
    Online
</div>
</header>
