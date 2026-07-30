<?php
declare(strict_types=1);

namespace BT\App\Provisioning\Coletor;

use BT\App\Provisioning\AbstractProvisioningService;
use BT\App\Provisioning\Contracts\ProvisioningInterface;

final class ColetorProvisioningService extends AbstractProvisioningService implements ProvisioningInterface
{
    protected function getProduto(): string
    {
        return 'BT1_COLETOR_PRO';
    }

    protected function getTipoLicenca(): string
    {
        return 'PRO';
    }
}
