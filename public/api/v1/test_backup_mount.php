<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
use BT\Core\Database\Database;

header('Content-Type: application/json');

try {
    $p = Database::fetch("SELECT * FROM platform LIMIT 1");
    if (!$p) throw new Exception("Configurações não encontradas.");

    $mountPoint = '/mnt/truenas/backups';

    // 1. Tenta escrever um arquivo de teste
    $testFile = $mountPoint . '/bt_write_test.txt';
    $canWrite = @file_put_contents($testFile, "Teste de Escrita Brandão Tech: " . date('Y-m-d H:i:s'));

    if ($canWrite) {
        @unlink($testFile);
        echo json_encode([
            'success' => true,
            'message' => '🚀 CONEXÃO ATIVA! O servidor conseguiu gravar dados no seu TrueNAS.',
            'output' => 'Pasta montada e com permissão de escrita.'
        ]);
    } else {
        // Se não conseguiu escrever, vamos ver se é porque não está montado
        $isMounted = (strpos(shell_exec('mount'), $mountPoint) !== false);

        echo json_encode([
            'success' => false,
            'message' => $isMounted ? '❌ ERRO DE PERMISSÃO: A pasta está conectada, mas o TrueNAS negou a gravação. Verifique as permissões do usuário no NAS.' : '🔴 DESCONECTADO: O TrueNAS não está montado na VM.',
            'output' => 'Dica: O comando de montagem automática só funciona via script de sistema (root) às 04h ou via terminal.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>
