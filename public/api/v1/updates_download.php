<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap/app.php';

use BT\App\Updates\UpdateController;

$releaseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$releaseId) {
    http_response_code(400);
    exit('Release OTA invalido.');
}

$release = (new UpdateController())->getById($releaseId);
if (!$release) {
    http_response_code(404);
    exit('Pacote OTA indisponivel.');
}

$filename = basename((string) $release['arquivo_path']);
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$subPasta = ($ext === 'zip') ? 'updates/' : 'apks/';
$path = dirname(__DIR__, 3) . '/storage/' . $subPasta . $filename;

if (!is_file($path)) {
    http_response_code(404);
    exit('Arquivo físico não encontrado no servidor.');
}

$contentType = ($ext === 'apk') ? 'application/vnd.android.package-archive' : 'application/zip';

header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, must-revalidate');

readfile($path);
