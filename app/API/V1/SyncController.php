<?php

declare(strict_types=1);

namespace BT\App\API\V1;

use BT\App\Services\SyncService;
use BT\Core\Http\Request;
use BT\Core\Http\JsonResponse;

final class SyncController
{
    public function handle(): void
    {
        $request = Request::capture();

        $service = new SyncService();

        $resultado = $service->sync($request);

        JsonResponse::success($resultado);
    }
}
