<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../../bootstrap/app.php';
require_once __DIR__ . '/../../../bootstrap/autoload.php';

use BT\App\Services\SyncService;
use BT\Core\Http\Request;

header('Content-Type: text/plain');

try {
    echo "🧪 TESTANDO SYNC SERVICE DIRETAMENTE\n\n";

    // Criamos uma classe de Request fake para injetar o payload
    class FakeRequest extends \BT\Core\Http\Request {
        public function __construct($data) {
            $ref = new ReflectionProperty(\BT\Core\Http\Request::class, 'json');
            $ref->setAccessible(true);
            $ref->setValue($this, $data);
        }
        public function method(): string { return 'POST'; }
        public function ip(): string { return '127.0.0.1'; }
    }

    $payload = [
        'uuid' => 'af945396-7f5a-4b4d-b65e-511505142dc5',
        'token' => '1043DBB812935D3CA10C7ADEC28B5FEA4CB04B22A9522E0CDEE0744C0A4B3F4E',
        'produto' => 'BT1_COLETOR_PRO',
        'versao' => '1.4.0',
        'device_uuid' => 'DMS8Z8RD9-78NGQ5'
    ];

    $request = new FakeRequest($payload);
    $service = new SyncService();

    echo "Executando sync()...\n";
    $result = $service->sync($request);

    echo "\nResultado:\n";
    print_r($result);

} catch (Throwable $e) {
    echo "\n❌ ERRO FATAL: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
