<?php

declare(strict_types=1);

namespace BT\App\Services;

final class LicenseFeatureService
{
    /**
     * Retorna os limites e recursos baseados no tipo de licença.
     */
    public function getFeatures(string $tipo): array
    {
        return match ($tipo) {
            'DEMONSTRACAO' => [
                'max_devices' => 2,
                'voice_enabled' => false,
                'custom_branding' => false,
                'reports' => 'NONE',
                'multi_ticket' => false,
                'hybrid_scheduling' => false
            ],
            'ENTERPRISE' => [
                'max_devices' => 10,
                'voice_enabled' => true,
                'custom_branding' => false,
                'reports' => 'BASIC',
                'multi_ticket' => true,
                'hybrid_scheduling' => false
            ],
            'PRO' => [
                'max_devices' => 25,
                'voice_enabled' => true,
                'custom_branding' => true,
                'reports' => 'ADVANCED',
                'multi_ticket' => true,
                'hybrid_scheduling' => true
            ],
            'PREMIUM' => [
                'max_devices' => 999,
                'voice_enabled' => true,
                'custom_branding' => true,
                'reports' => 'FULL',
                'multi_ticket' => true,
                'hybrid_scheduling' => true
            ],
            default => [
                'max_devices' => 1,
                'voice_enabled' => false,
                'custom_branding' => false,
                'reports' => 'NONE',
                'multi_ticket' => false,
                'hybrid_scheduling' => false
            ]
        };
    }

    /**
     * Verifica se a instalação pode adicionar um novo dispositivo.
     */
    public function canAddDevice(string $tipo, int $currentCount): bool
    {
        $features = $this->getFeatures($tipo);
        return $currentCount < $features['max_devices'];
    }
}
