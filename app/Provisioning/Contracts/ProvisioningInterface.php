<?php
declare(strict_types=1);

namespace BT\App\Provisioning\Contracts;

interface ProvisioningInterface
{
    public function criarInstalacao(array $dados): array;
}
