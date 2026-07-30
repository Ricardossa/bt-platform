<?php

declare(strict_types=1);

namespace BT\App\Services;

use BT\Core\Database\Database;
use BT\Core\Http\Request;

final class DeviceService
{
    public function buscarPorInstalacaoEId(
        int $instalacaoId,
        string $deviceUuid
    ): ?array {
        return Database::fetch(
            "SELECT *
             FROM dispositivos
             WHERE instalacao_id = ?
             AND device_uuid = ?
             AND ativo = 1",
            [$instalacaoId, $deviceUuid]
        );
    }

    public function contarAtivos(int $instalacaoId): int
    {
        $result = Database::fetch(
            "SELECT COUNT(*) as total
             FROM dispositivos
             WHERE instalacao_id = ?
             AND ativo = 1",
            [$instalacaoId]
        );

        return (int) ($result['total'] ?? 0);
    }

    public function registrar(
        int $instalacaoId,
        string $deviceUuid,
        Request $request
    ): int {
        $fabricante = (string) $request->input('device.fabricante');
        $modelo = (string) $request->input('device.modelo');
        $android = (string) $request->input('device.android');
        $versaoApp = (string) $request->input('versao');
        $ip = $request->ip();

        Database::execute(
            "INSERT INTO dispositivos (
                instalacao_id,
                device_uuid,
                fabricante,
                modelo,
                android,
                versao_app,
                ultimo_ip,
                ultima_sincronizacao,
                status,
                ativo
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, NOW(), 'ONLINE', 1
            )",
            [
                $instalacaoId,
                $deviceUuid,
                $fabricante ?: null,
                $modelo ?: null,
                $android ?: null,
                $versaoApp ?: null,
                $ip ?: null
            ]
        );

        return (int) Database::lastInsertId();
    }

    public function atualizar(
        int $dispositivoId,
        Request $request
    ): void {
        $versaoApp = (string) $request->input('versao');
        $ip = $request->ip();
        $bateria = (int) $request->input('device.bateria');
        $wifi = (string) $request->input('device.wifi');

        $bateria = $bateria > 0 ? $bateria : null;

        Database::execute(
            "UPDATE dispositivos
             SET
                versao_app = ?,
                ultimo_ip = ?,
                ultima_sincronizacao = NOW(),
                bateria = ?,
                wifi = ?,
                status = 'ONLINE'
             WHERE id = ?",
            [
                $versaoApp ?: null,
                $ip ?: null,
                $bateria,
                $wifi ?: null,
                $dispositivoId
            ]
        );
    }

    public function registrarOuAtualizar(
        int $instalacaoId,
        Request $request
    ): array {
        $deviceUuid = (string) $request->input('device_uuid');

        if (empty($deviceUuid)) {
            return [
                'success' => false,
                'message' => 'Device UUID é obrigatório.',
                'code' => 'DEVICE_UUID_REQUIRED'
            ];
        }

        $dispositivo = $this->buscarPorInstalacaoEId(
            $instalacaoId,
            $deviceUuid
        );

        if ($dispositivo === null) {
            $id = $this->registrar(
                $instalacaoId,
                $deviceUuid,
                $request
            );
            return [
                'success' => true,
                'evento' => 'REGISTERED',
                'dispositivo_id' => $id
            ];
        }

        $this->atualizar(
            (int) $dispositivo['id'],
            $request
        );

        return [
            'success' => true,
            'evento' => 'UPDATED',
            'dispositivo_id' => (int) $dispositivo['id']
        ];
    }

    /**
     * Desativa um dispositivo (Soft Delete) para liberar vaga na licença.
     */
    public function remover(int $id): bool
    {
        return Database::execute(
            "UPDATE dispositivos SET ativo = 0, status = 'BLOQUEADO' WHERE id = ?",
            [$id]
        );
    }
}