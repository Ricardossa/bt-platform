<?php

declare(strict_types=1);

namespace BT\App\Auth;

use BT\Core\Database\Database;
use BT\App\Services\ActivityService;

final class Auth
{
    private const SESSION_KEY = 'bt_platform_user';

    public static function attempt(string $login, string $senha): bool
    {
        $usuario = Database::fetch(
            "SELECT *
             FROM usuarios_master
             WHERE login = ?
               AND ativo = 1
             LIMIT 1",
            [$login]
        );

        if (!$usuario) {
            return false;
        }

        if (!password_verify($senha, $usuario['senha'])) {
            return false;
        }

        unset($usuario['senha']);

        Session::set(self::SESSION_KEY, $usuario);

        ActivityService::log('INFO', 'SYSTEM', "Usuário logado: {$usuario['nome']}", [], $usuario['nome']);

        return true;
    }

    public static function check(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    public static function user(): ?array
    {
        return Session::get(self::SESSION_KEY);
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user !== null && strtoupper((string) ($user['nivel'] ?? '')) === 'ADMIN';
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            http_response_code(403);
            exit('Acesso restrito a administradores.');
        }
    }

    public static function logout(): void
    {
        Session::remove(self::SESSION_KEY);
    }
}
