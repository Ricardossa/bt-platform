<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap/app.php';
require_once __DIR__ . '/../../../bootstrap/auth.php';

use BT\App\Auth\Auth;
use BT\App\Services\MasterAIService;

header('Content-Type: application/json; charset=utf-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado.']);
    exit;
}

try {
    $service = new MasterAIService();
    $briefing = $service->getHealthBriefing();

    echo json_encode([
        'success' => true,
        'briefing' => $briefing
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
