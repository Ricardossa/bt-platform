<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| BRAND TECH INTEGRATION
| BT Queue Enterprise
|--------------------------------------------------------------------------
| Componente oficial de identidade visual.
| Responsável por carregar empresa e logo.
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__, 3) . '/core/Database.php';

use BTQueue\Core\Database;

$config = [];

try {

    $linhas = Database::fetchAll(
        "SELECT chave, valor FROM configuracoes"
    );

    foreach ($linhas as $linha) {
        $config[$linha['chave']] = $linha['valor'];
    }

} catch (Throwable $e) {
    die($e->getMessage());
}

$empresa = $config['empresa'] ?? 'BT Queue Enterprise';

$logoPath = dirname(__DIR__, 2) . '/uploads/logo.png';

$logoExiste = is_file($logoPath);

$logoUrl = '/painel_v4/public/uploads/logo.png?v=' . filemtime($logoPath);
