<?php

declare(strict_types=1);

require_once __DIR__ . '/platform_brand.php';

$pageTitle = $pageTitle ?? $empresa;
$currentPage = basename($_SERVER['PHP_SELF']);

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
                    max-width:180px;
                    max-height:80px;
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

        <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>
            Dashboard
        </a>

        <a href="platform.php" class="<?= $currentPage === 'platform.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-gear"></i>
            Platform
        </a>

        <a href="empresas.php" class="<?= $currentPage === 'empresas.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-building"></i>
            Empresas
        </a>

        <a href="unidades.php" class="<?= $currentPage === 'unidades.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-server"></i>
            Unidades SaaS
        </a>

        <a href="instalacoes.php" class="<?= $currentPage === 'instalacoes.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-microchip"></i>
            Instalações
        </a>

        <a href="produtos.php" class="<?= $currentPage === 'produtos.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-boxes-stacked"></i>
            Produtos
        </a>

        <a href="licencas.php" class="<?= $currentPage === 'licencas.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-key"></i>
            Licenças
        </a>

        <a href="financeiro.php" class="<?= $currentPage === 'financeiro.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-sack-dollar"></i>
            Financeiro
        </a>

        <a href="precos.php" class="<?= ($currentPage === 'precos.php' || $currentPage === 'planos_gerenciar.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-tags"></i>
            Ofertas Comerciais
        </a>

        <a href="updates.php" class="<?= $currentPage === 'updates.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-rocket"></i>
            Updates OTA
        </a>

        <a href="gerador_pacote.php" class="<?= $currentPage === 'gerador_pacote.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-box-archive"></i>
            Gerador de Pacotes
        </a>

        <a href="downloads.php" class="<?= $currentPage === 'downloads.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-mobile-screen-button"></i>
            Downloads APK
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
