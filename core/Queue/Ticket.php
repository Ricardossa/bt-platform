<?php

declare(strict_types=1);

namespace BT\Core\Queue;

final class Ticket
{
    public function __construct(
        public readonly string $number,
        public readonly string $service,
        public readonly int $priority = 0,
        public readonly string $status = 'waiting'
    ) {
    }
}
