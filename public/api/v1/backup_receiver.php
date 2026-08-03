<?php
declare(strict_types=1);

/**
 * BT Platform - Backup Receiver (v1.0)
 * Recebe e gerencia backups enviados pelas unidades Enterprise.
 */

require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\Core\Database\Database;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Apenas POST é permitido.']);
    exit;
}

$uuid = trim($_POST['uuid'] ?? '');
$token = trim($_POST['token'] ?? '');

if (empty($uuid) || empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Identidade (UUID/Token) não informada.']);
    exit;
}

// 1. Validação de Segurança
$instalacao = Database::fetch("SELECT id FROM instalacoes WHERE uuid = ? AND token = ? LIMIT 1", [$uuid, $token]);
if (!$instalacao) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado: Identidade inválida.']);
    exit;
}

// 2. Processamento do Arquivo
if (!isset($_FILES['backup']) || $_FILES['backup']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Arquivo de backup não recebido corretamente.']);
    exit;
}

$storageDir = BT_ROOT . '/storage/backups/' . $uuid . '/';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0775, true);
}

$filename = date('Ymd_His') . '_banco.zip';
$targetPath = $storageDir . $filename;

if (move_uploaded_file($_FILES['backup']['tmp_name'], $targetPath)) {

    // 3. Rotação Inteligente (Mantém apenas os últimos 3)
    $files = glob($storageDir . '*.zip');
    if (count($files) > 3) {
        array_multisort(array_map('filemtime', $files), SORT_ASC, $files);
        while (count($files) > 3) {
            $oldFile = array_shift($files);
            @unlink($oldFile);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Backup recebido e arquivado com sucesso.',
        'filename' => $filename
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar backup no storage da Master.']);
}
