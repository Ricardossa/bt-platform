<?php
declare(strict_types=1);

namespace BT\Core\Security;

final class LicenseKeyGenerator
{
    /**
     * Gera uma chave no formato:
     * BTQ-8A2F-91CD-77BE-14AA
     */
    public static function generate(): string
    {
        return sprintf(
            'BTQ-%s-%s-%s-%s',
            self::block(),
            self::block(),
            self::block(),
            self::block()
        );
    }

    /**
     * Gera um bloco hexadecimal de 4 caracteres.
     */
    private static function block(): string
    {
        return strtoupper(
            substr(bin2hex(random_bytes(2)), 0, 4)
        );
    }
}
