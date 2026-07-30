<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap/app.php';

use BT\App\Updates\UpdateController;
use BT\Core\Http\JsonResponse;

try {
    $controller = new UpdateController();
    $latest = $controller->getLatest();

    if (!$latest) {
        JsonResponse::success(['update_available' => false]);
        exit;
    }

    $currentVersion = $_GET['v'] ?? '0.0.0';

    // Limpeza de 'v' inicial para garantir comparação correta
    $latestClean = ltrim(strtolower($latest['versao']), 'v');
    $currentClean = ltrim(strtolower($currentVersion), 'v');

    if (version_compare($latestClean, $currentClean, '>')) {

        // DETECÇÃO DE URL COMPLETA PARA CLIENTES ANTIGOS (BRIDGE)
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        $protocol = $isHttps ? "https" : "http";
        $fullUrl = "$protocol://{$_SERVER['HTTP_HOST']}/api/v1/updates_download.php?id=" . (int)$latest['id'];

        JsonResponse::success([
            'update_available' => true,
            'version' => $latestClean,
            'release_id' => (int) $latest['id'],
            'url' => $fullUrl, // Campo vital para compatibilidade com clientes antigos
            'download_path' => '/api/v1/updates_download.php?id=' . (int) $latest['id'],
            'sha256' => $latest['checksum_sha256'],
            'mandatory' => (bool)$latest['is_mandatory'],
            'changelog' => $latest['changelog'],
            'channel' => $latest['canal'] ?? 'stable'
        ]);
    } else {
        JsonResponse::success(['update_available' => false, 'debug' => "Master: $latestClean, Client: $currentClean"]);
    }

} catch (Exception $e) {
    JsonResponse::error($e->getMessage());
}
