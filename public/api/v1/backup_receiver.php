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

$hashEnviado = trim($_POST['hash'] ?? '');

// 1. Validação de Segurança e Identificação Humana
$instalacao = Database::fetch("
    SELECT i.id, i.nome as unidade_nome, e.nome_fantasia as empresa_nome
    FROM instalacoes i
    JOIN empresas e ON e.id = i.empresa_id
    WHERE i.uuid = ? AND i.token = ? LIMIT 1
", [$uuid, $token]);

if (!$instalacao) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado: Identidade inválida.']);
    exit;
}

// 2. Organização de Pastas Inteligente
$empresaSlug = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '_', (string)$instalacao['empresa_nome']));
$unidadeSlug = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '_', (string)$instalacao['unidade_nome']));

$storageDir = BT_ROOT . "/storage/backups/{$empresaSlug}/{$unidadeSlug}/";
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0775, true);
}

// 3. Processamento do Arquivo
if (!isset($_FILES['backup']) || $_FILES['backup']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Arquivo de backup não recebido corretamente.']);
    exit;
}

$tempFile = $_FILES['backup']['tmp_name'];

// 4. Validação de Integridade (v2.6.0)
if (!empty($hashEnviado)) {
    $hashLocal = hash_file('sha256', $tempFile);
    if ($hashLocal !== $hashEnviado) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Falha de integridade: o arquivo chegou corrompido.']);
        exit;
    }
}

$filename = date('Ymd_His') . '_banco.sql.gz';
$targetPath = $storageDir . $filename;

if (move_uploaded_file($tempFile, $targetPath)) {

    // 5. Política de Retenção GFS (v2.6.0)
    // - 7 Diários, 4 Semanais, 3 Mensais
    rotacionarBackups($storageDir);

    echo json_encode([
        'success' => true,
        'message' => 'SafeBackup arquivado e validado com sucesso.',
        'filename' => $filename
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar backup no storage da Master.']);
}

/**
 * Lógica de Retenção GFS (Grandfather-Father-Son)
 */
function rotacionarBackups(string $dir): void
{
    $files = glob($dir . '*.sql.gz');
    if (!$files) return;

    $now = time();
    $day = 86400;

    foreach ($files as $file) {
        $mtime = filemtime($file);
        $ageDays = floor(($now - $mtime) / $day);

        $dateInfo = getdate($mtime);
        $isSunday = ($dateInfo['wday'] === 0);
        $isFirstOfMonth = ($dateInfo['mday'] === 1);

        $manter = false;

        // Regra 1: Manter últimos 7 dias (Diários)
        if ($ageDays <= 7) $manter = true;

        // Regra 2: Manter os últimos 4 Domingos (Semanais)
        if ($isSunday && $ageDays <= 30) $manter = true;

        // Regra 3: Manter os últimos 3 meses (Mensais - dia 01)
        if ($isFirstOfMonth && $ageDays <= 90) $manter = true;

        if (!$manter) {
            @unlink($file);
        }
    }
}
