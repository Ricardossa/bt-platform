<?php
header('Content-Type: text/plain');
$logFile = '/var/log/apache2/access.log';
if (!is_readable($logFile)) {
    die("Erro: Nao consigo ler o log em $logFile. Verifique permissoes.");
}

$lines = file($logFile);
$lastLines = array_slice($lines, -100);

echo "--- ULTIMAS TENTATIVAS DE SYNC NO APACHE ---\n";
foreach ($lastLines as $line) {
    if (strpos($line, 'sync.php') !== false) {
        echo $line;
    }
}
