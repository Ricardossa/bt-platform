<?php

declare(strict_types=1);

namespace BT\App\Auth;

final class LoginController
{
    public function login(string $login, string $senha): array
    {
        if ($login === '' || $senha === '') {
            return [
                'success' => false,
                'message' => 'Informe usuário e senha.'
            ];
        }

        if (!Auth::attempt($login, $senha)) {
            return [
                'success' => false,
                'message' => 'Usuário ou senha inválidos.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Login realizado com sucesso.'
        ];
    }
}
