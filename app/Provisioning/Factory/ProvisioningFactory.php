<?php
declare(strict_types=1);

namespace BT\App\Provisioning\Factory;

use BT\App\Provisioning\Queue\QueueProvisioningService;
use BT\App\Provisioning\Coletor\ColetorProvisioningService;
use BT\App\Provisioning\Contracts\ProvisioningInterface;

final class ProvisioningFactory
{
    public static function make(string $produto): ProvisioningInterface
    {
        switch ($produto) {
            case 'BT1_COLETOR_PRO':
                return new ColetorProvisioningService();
            
            case 'BT_QUEUE_ENTERPRISE':
            default:
                return new QueueProvisioningService();
        }
    }
}
