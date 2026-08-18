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

        <a href="unidades.php" style="background:rgba(29, 180, 255, 0.1); border-radius:10px; margin-top:5px;">
            <i class="fa-solid fa-server" style="color:var(--secondary);"></i>
            Unidades SaaS
        </a>

        <a href="licencas.php">
            <i class="fa-solid fa-key"></i>
            Licenças
        </a>

        <a href="financeiro.php">
            <i class="fa-solid fa-sack-dollar"></i>
            Financeiro
        </a>

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
