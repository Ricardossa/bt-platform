<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../../../bootstrap/app.php';
    require_once __DIR__ . '/../../../bootstrap/autoload.php';

    $payload = [
        'uuid' => 'af945396-7f5a-4b4d-b65e-511505142dc5',
        'token' => '1043DBB812935D3CA10C7ADEC28B5FEA4CB04B22A9522E0CDEE0744C0A4B3F4E',
        'produto' => 'BT1_COLETOR_PRO',
        'versao' => '1.4.0',
        'device_uuid' => 'DMS8Z8RD9-78NGQ5'
    ];

    $_POST = $payload;
    // Simula php://input se possível? No, just use $_POST if Request handles it.

    echo "Capturing Request...\n";
    $request = \BT\Core\Http\Request::capture();

    echo "Running SyncService...\n";
    $service = new \BT\App\Services\SyncService();
    $result = $service->sync($request);

    echo "Result:\n";
    print_r($result);

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
?>
