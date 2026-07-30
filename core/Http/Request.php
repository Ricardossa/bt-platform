<?php

declare(strict_types=1);

namespace BT\Core\Http;

final class Request
{
    private array $json = [];

    private int $jsonError = JSON_ERROR_NONE;

    public function __construct()
    {
        $content = file_get_contents('php://input');

        // Tenta decodificar JSON
        $this->json = json_decode($content, true) ?? [];

        // Se o JSON falhar, tenta ler do $_POST (formulÃ¡rio tradicional)
        if (empty($this->json) && !empty($_POST)) {
            $this->json = $_POST;
        }

        $this->jsonError = json_last_error();
    }

    public static function capture(): self
    {
        return new self();
    }

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public function input(
        string $key,
        mixed $default = null
    ): mixed {

        return $this->json[$key] ?? $default;

    }

    public function has(
        string $key
    ): bool {

        return array_key_exists(
            $key,
            $this->json
        );

    }


    public function all(): array
    {
        return $this->json;
    }

        public function header(
        string $name
    ): ?string {

        $name = strtoupper(
            str_replace('-', '_', $name)
        );

        if ($name === 'CONTENT_TYPE') {
            return $_SERVER['CONTENT_TYPE'] ?? null;
        }

        if ($name === 'CONTENT_LENGTH') {
            return $_SERVER['CONTENT_LENGTH'] ?? null;
        }

        return $_SERVER['HTTP_' . $name] ?? null;
    }



    public function isValidJson(): bool
    {
        return $this->jsonError === JSON_ERROR_NONE;
    }

    public function jsonError(): int
    {
        return $this->jsonError;
    }

}