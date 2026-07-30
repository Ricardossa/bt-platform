<?php

declare(strict_types=1);

use BT\Core\Database\Database;

$platform = Database::fetch(
    "SELECT
        nome_empresa,
        logo
     FROM platform
     LIMIT 1"
);

$empresa = $platform['nome_empresa']
    ?? 'BT Queue Platform';

$logoExiste = false;

$logoUrl = '';

if (!empty($platform['logo'])) {

    $arquivo = __DIR__
        . '/../uploads/logo/'
        . $platform['logo'];

    if (is_file($arquivo)) {

        $logoExiste = true;

        $logoUrl = '/uploads/logo/'
            . $platform['logo'];

    }

}

$versao = '2.0.0-dev';
