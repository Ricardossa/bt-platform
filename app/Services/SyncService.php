<?php

declare(strict_types=1);

namespace BT\App\Services;

use BT\Core\Http\Request;
use BT\Core\Database\Database;
use BT\App\Services\DeviceService;
use BT\App\Services\LicenseFeatureService;
use BT\App\Services\ActivityService;

final class SyncService
{
    private DeviceService $deviceService;
    private LicenseFeatureService $featureService;

    public function __construct()
    {
        $this->deviceService = new DeviceService();
        $this->featureService = new LicenseFeatureService();
    }

    private function buscarInstalacao(
        string $uuid,
        string $produto
    ): ?array {

        return Database::fetch(
            "SELECT *
             FROM instalacoes
             WHERE uuid = ?
             AND produto = ?",
            [$uuid, $produto]
        );

    }

    private function validarToken(
        array $instalacao,
        string $token
    ): bool
    {
        return hash_equals(
            $instalacao['token'],
            $token
        );
    }

    private function validarLicenca(array $licenca): ?array
    {
        switch ($licenca['status']) {

            case 'ATIVA':
                return null;

            case 'SUSPENSA':
                return [
                    'status'  => 'ERROR',
                    'code'    => 'LICENSE_SUSPENDED',
                    'message' => 'Licença suspensa.'
                ];

            case 'REVOGADA':
                return [
                    'status'  => 'ERROR',
                    'code'    => 'LICENSE_REVOKED',
                    'message' => 'Licença revogada.'
                ];

            case 'EXPIRADA':
                return [
                    'status'  => 'ERROR',
                    'code'    => 'LICENSE_EXPIRED',
                    'message' => 'Licença expirada.'
                ];

            default:
                return [
                    'status'  => 'ERROR',
                    'code'    => 'LICENSE_INVALID',
                    'message' => 'Status da licença inválido.'
                ];
        }
    }

    private function atualizarPresenca(
        int $instalacaoId,
        Request $request
    ): void
    {
        Database::execute(
            "UPDATE instalacoes
             SET
                versao = ?,
                status = 'ONLINE',
                ultima_sincronizacao = NOW(),
                ultimo_ip = ?
             WHERE id = ?",
            [
                (string) $request->input('versao'),
                $request->ip(),
                $instalacaoId
            ]
        );
    }

    public function sync(Request $request): array
    {
        $uuid = (string) $request->input('uuid');
        $produto = (string) $request->input('produto');

        // [COMPATIBILIDADE] Se o produto não for enviado (versões antigas), assume BT_QUEUE_ENTERPRISE
        if (empty($produto)) {
            $produto = 'BT_QUEUE_ENTERPRISE';
        }

        $instalacao = $this->buscarInstalacao(
            $uuid,
            $produto
        );

        if ($instalacao === null) {
            return [
                'status' => 'ERROR',
                'code' => 'INSTALLATION_NOT_FOUND',
                'message' => 'Instalação não encontrada.'
            ];
        }

        $token = (string) $request->input('token');

        if (!$this->validarToken($instalacao, $token)) {
            return [
                'status' => 'ERROR',
                'code' => 'INVALID_TOKEN',
                'message' => 'Token inválido.'
            ];
        }

        $licenca = Database::fetch(
            "SELECT * FROM licencas WHERE instalacao_id = ?",
            [(int) $instalacao['id']]
        );

        if ($licenca === null) {
            return [
                'status' => 'ERROR',
                'code' => 'LICENSE_NOT_FOUND',
                'message' => 'Licença não encontrada.'
            ];
        }

        $features = $this->featureService->getFeatures($licenca['tipo']);

        $deviceUuid = (string) $request->input('device_uuid');
        $dispositivoExistente = $this->deviceService->buscarPorInstalacaoEId(
            (int) $instalacao['id'],
            $deviceUuid
        );

        if ($dispositivoExistente === null) {
            $totalAtivos = $this->deviceService->contarAtivos((int) $instalacao['id']);

            if (!$this->featureService->canAddDevice($licenca['tipo'], $totalAtivos)) {
                ActivityService::log('WARNING', 'DEVICE', "Limite de dispositivos atingido: {$instalacao['nome']}");
                return [
                    'status' => 'ERROR',
                    'code' => 'DEVICE_LIMIT_REACHED',
                    'message' => 'Limite de dispositivos atingido para este plano (' . $features['max_devices'] . ').'
                ];
            }

            ActivityService::log('SUCCESS', 'DEVICE', "Novo dispositivo ativado: {$instalacao['nome']} ({$request->input('device.modelo')})", [
                'instalacao_id' => $instalacao['id'],
                'device_uuid' => $deviceUuid
            ], 'Sistema');
        }

        $resultadoDispositivo = $this->deviceService->registrarOuAtualizar(
            (int) $instalacao['id'],
            $request
        );

        if (!$resultadoDispositivo['success']) {
            return [
                'status' => 'ERROR',
                'code' => $resultadoDispositivo['code'],
                'message' => $resultadoDispositivo['message']
            ];
        }

        $this->atualizarPresenca(
            (int) $instalacao['id'],
            $request
        );

        ActivityService::log('INFO', 'SYNC', "Sincronização realizada: {$instalacao['nome']}", [
            'instalacao_id' => $instalacao['id'],
            'versao' => $request->input('versao')
        ], $instalacao['nome']);

        return [
            'status' => 'OK',
            'sync' => [
                'id' => bin2hex(random_bytes(4)),
                'server' => [
                    'time' => gmdate('c'),
                    'heartbeat' => 60,
                    'platform' => '2.0.0'
                ],
                'license' => [
                    'status'  => $licenca['status'],
                    'type'    => $licenca['tipo'],
                    'expires' => $licenca['data_validade']
                ],
                'features' => $features,
                'update' => [
                    'available' => false
                ],
                'configuration' => [
                    'changed' => false
                ],
                'commands' => [],
                'messages' => []
            ]
        ];
    }
}
