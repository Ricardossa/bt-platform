<?php

declare(strict_types=1);

namespace BT\Core\Http;

final class JsonResponse
{
    public static function send(
        array $data,
        int $status = 200
    ): void {

        http_response_code($status);

        header(
            'Content-Type: application/json; charset=utf-8'
        );

        echo json_encode(
            $data,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        exit;
    }

    public static function success(
        array $data = []
    ): void {

        self::send($data, 200);

    }

    public static function error(
        string $message,
        int $status = 400
    ): void {

        self::send([

            'status' => 'ERROR',

            'message' => $message

        ], $status);
    }
}
