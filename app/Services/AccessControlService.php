<?php

namespace App\Services;

use App\Models\Log;
use App\Models\QrUser;
use App\Models\User;
use App\Services\Door\DoorControllerInterface;
use App\Services\Messaging\AccessEvent;
use App\Services\Messaging\AccessEventPublisher;
use Illuminate\Http\Request;

class AccessControlService
{
    public function __construct(
        private readonly DoorControllerInterface $door,
        private readonly AccessEventPublisher $publisher,
    ) {}

    public function grant(User $user, QrUser $qr, Request $request): void
    {
        $qr->update(['active' => false]);

        $this->door->grantAccess();

        $this->publisher->publish(new AccessEvent(
            eventType: 'qr.granted',
            message: 'Получен доступ к двери',
            logType: Log::SUCCESS,
            userId: $user->id,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            source: 'qr',
            grantDoor: false,
            meta: ['qr_id' => $qr->id, 'unique_id' => $qr->unique_id],
        ));
    }

    public function deny(string $eventType, string $message, int $logType, Request $request, ?User $user = null): void
    {
        $this->door->denyAccess();

        $this->publisher->publish(new AccessEvent(
            eventType: $eventType,
            message: $message,
            logType: $logType,
            userId: $user?->id,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            source: 'qr',
            grantDoor: false,
        ));
    }

    public function record(string $eventType, string $message, int $logType, Request $request, ?User $user = null, string $source = 'app'): void
    {
        $this->publisher->publish(new AccessEvent(
            eventType: $eventType,
            message: $message,
            logType: $logType,
            userId: $user?->id,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            source: $source,
            grantDoor: false,
        ));
    }
}
