<?php

declare(strict_types=1);

namespace BT\Core\Middleware;

use BT\Core\Http\Request;
use BT\Core\Http\JsonResponse;

final class PostMiddleware
{
    public static function handle(
        Request $request
    ): void {

        if ($request->method() !== 'POST') {

            JsonResponse::error(
                'Method Not Allowed',
                405
            );

        }

    }
}
