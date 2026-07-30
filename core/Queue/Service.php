<?php

declare(strict_types=1);

namespace BT\Core\Queue;

final class Service
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $prefix,
        public readonly int $priority = 0,
        public readonly bool $enabled = true
    ) {
    }
}
