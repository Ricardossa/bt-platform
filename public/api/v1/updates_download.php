<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap/app.php';

use BT\App\Updates\UpdateController;

$idOrName = $_GET['id'] ?? '';

if (is_numeric($idOrName)) {
    $releaseId = (int)$idOrName;
    $release = (new UpdateController())->getById($releaseId);
    if (!$release) {
        http_response_code(404);
        exit('Pacote OTA indisponivel.');
    }
    $filename = basename((string) $release['arquivo_path']);
} else {
    // [v2.7.3] Suporte a download direto por nome (Gerador UI)
    $filename = basename($idOrName);
    if (empty($filename) || !str_ends_with($filename, '.zip')) {
        http_response_code(400);
        exit('Nome de pacote invalido.');
    }
}

$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$subPasta = ($ext === 'zip') ? 'updates/' : 'apks/';
$path = dirname(__DIR__, 3) . '/storage/' . $subPasta . $filename;

if (!is_file($path)) {
    http_response_code(404);
    exit('Arquivo físico não encontrado no servidor: ' . $filename);
}

$contentType = ($ext === 'apk') ? 'application/vnd.android.package-archive' : 'application/zip';

header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, must-revalidate');

readfile($path);
