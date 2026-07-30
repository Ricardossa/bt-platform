<?php
declare(strict_types=1);

namespace BT\App\Provisioning\Queue;

use BT\App\Provisioning\AbstractProvisioningService;
use BT\App\Provisioning\Contracts\ProvisioningInterface;

final class QueueProvisioningService extends AbstractProvisioningService implements ProvisioningInterface
{
    protected function getProduto(): string
    {
        return 'BT_QUEUE_ENTERPRISE';
    }

    protected function getTipoLicenca(): string
    {
        return 'ENTERPRISE';
    }
}
