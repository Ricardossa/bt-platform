<?php

declare(strict_types=1);

namespace BT\App\API\V1;

use BT\Core\Database\Database;
use BT\Core\Http\Request;
use BT\Core\Http\JsonResponse;
use Exception;

final class ActivationController
{
    public function handle(): void
    {
        try {
            $request = Request::capture();
            $codigo = strtoupper(trim((string)$request->input('codigo')));

            if (empty($codigo)) {
                throw new Exception("Código de ativação é obrigatório.");
            }

            // Busca a instalação pelo código de ativação válido
            $instalacao = Database::fetch(
                "SELECT uuid, token
                 FROM instalacoes
                 WHERE codigo_ativacao = ?
                 AND expiracao_ativacao > NOW()
                 LIMIT 1",
                [$codigo]
            );

            if (!$instalacao) {
                throw new Exception("Código inválido ou expirado.");
            }

            // --- INTELIGÊNCIA MASTER: Marcar como ativado e limpar PIN ---
            Database::execute(
                "UPDATE instalacoes
                 SET codigo_ativacao = NULL,
                     status = 'ONLINE',
                     ultima_sincronizacao = NOW()
                 WHERE codigo_ativacao = ?",
                [$codigo]
            );

            JsonResponse::success([
                'uuid' => $instalacao['uuid'],
                'token' => $instalacao['token']
            ]);

        } catch (Exception $e) {
            JsonResponse::error($e->getMessage(), 400);
        }
    }
}
