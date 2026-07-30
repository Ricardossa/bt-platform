<?php

declare(strict_types=1);

namespace BT\Core\Queue;

final class QueueEngine
{
    public function createTicket(array $data = []): array
    {
        return [
            'status'    => true,
            'action'    => 'ticket.created',
            'data'      => $data,
            'createdAt' => date('Y-m-d H:i:s'),
        ];
    }

    public function callNext(): array
    {
        return [
            'status' => true,
            'action' => 'ticket.called',
        ];
    }

    public function recall(): array
    {
        return [
            'status' => true,
            'action' => 'ticket.recalled',
        ];
    }

    public function finishTicket(): array
    {
        return [
            'status' => true,
            'action' => 'ticket.finished',
        ];
    }

    public function cancelTicket(): array
    {
        return [
            'status' => true,
            'action' => 'ticket.cancelled',
        ];
    }

    public function estimateWaitingTime(): int
    {
        return 0;
    }
}
