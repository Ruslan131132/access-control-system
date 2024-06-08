<?php

namespace App\Services\Messaging;

use App\Models\Log as AccessLog;
use App\Services\Door\DoorControllerInterface;

class AccessEventProcessor
{
    public function __construct(
        private readonly DoorControllerInterface $door,
    ) {}

    public function handle(AccessEvent $event): void
    {
        AccessLog::create([
            'type' => $event->logType,
            'text' => $event->message,
            'user_id' => $event->userId,
            'ip' => $event->ip,
            'user_agent' => $event->userAgent,
            'source' => $event->source,
        ]);

        if ($event->grantDoor) {
            $this->door->grantAccess();

            return;
        }

        if ($event->denyDoor) {
            $this->door->denyAccess();
        }
    }
}
