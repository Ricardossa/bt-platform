<?php

declare(strict_types=1);

namespace BT\Core\Middleware;

use BT\Core\Http\Request;
use BT\Core\Http\JsonResponse;

final class JsonMiddleware
{
    public static function handle(
        Request $request
    ): void {

        $contentType = $request->header('Content-Type');

        if ($contentType === null) {

            JsonResponse::error(
                'Content-Type header is required',
                400
            );

        }

        if (
            stripos(
                $contentType,
                'application/json'
            ) === false
        ) {

            JsonResponse::error(
                'Content-Type must be application/json',
                415
            );

        }

        if (!$request->isValidJson()) {

            JsonResponse::error(
                'Invalid JSON',
                400
            );

        }

    }
}
