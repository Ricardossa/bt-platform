<?php
declare(strict_types=1);

namespace BT\App\Licenca;

use BT\Core\Database\Database;
use BT\Core\Security\LicenseKeyGenerator;
use BT\App\Services\ActivityService;
use BT\App\Auth\Auth;

final class LicencaService
{
    public function criar(array $dados): bool
    {
        $result = Database::execute(
            "INSERT INTO licencas
            (
                instalacao_id,
                chave_licenca,
                tipo,
                status,
                data_ativacao,
                data_validade,
                ultima_validacao
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?
            )",
            [
                $dados['instalacao_id'] ?? null,
                LicenseKeyGenerator::generate(),
                $dados['tipo'] ?? null,
                $dados['status'] ?? null,
                $dados['data_ativacao'] ?? null,
                $dados['data_validade'] ?? null,
                $dados['ultima_validacao'] ?? null
            ]
        );

        if ($result) {
            $user = Auth::user()['nome'] ?? 'Sistema';
            ActivityService::log('SUCCESS', 'LICENSE', "Nova licença {$dados['tipo']} criada para instalação #{$dados['instalacao_id']}", [], $user);
        }

        return $result;
    }
}
