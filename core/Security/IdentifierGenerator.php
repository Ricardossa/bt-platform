<?php

declare(strict_types=1);

namespace BT\Core\Security;

final class IdentifierGenerator
{
    public static function uuid(): string
    {
        $data = random_bytes(16);

        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf(
            '%s%s-%s-%s-%s-%s%s%s',
            str_split(bin2hex($data), 4)
        );
    }

    public static function token(): string
    {
        return strtoupper(
            bin2hex(random_bytes(32))
        );
    }

    public static function license(): string
    {
        return LicenseKeyGenerator::generate();
    }
}
