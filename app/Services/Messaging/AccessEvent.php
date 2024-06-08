<?php

namespace App\Services\Messaging;

final class AccessEvent
{
    public function __construct(
        public readonly string $eventType,
        public readonly string $message,
        public readonly int $logType,
        public readonly ?int $userId = null,
        public readonly ?string $ip = null,
        public readonly ?string $userAgent = null,
        public readonly string $source = 'app',
        public readonly bool $grantDoor = false,
        public readonly bool $denyDoor = false,
        public readonly array $meta = [],
    ) {}

    public function toArray(): array
    {
        return [
            'event_type' => $this->eventType,
            'message' => $this->message,
            'log_type' => $this->logType,
            'user_id' => $this->userId,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'source' => $this->source,
            'grant_door' => $this->grantDoor,
            'deny_door' => $this->denyDoor,
            'meta' => $this->meta,
            'occurred_at' => now()->toIso8601String(),
        ];
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            eventType: (string) ($payload['event_type'] ?? 'unknown'),
            message: (string) ($payload['message'] ?? ''),
            logType: (int) ($payload['log_type'] ?? 1),
            userId: isset($payload['user_id']) ? (int) $payload['user_id'] : null,
            ip: $payload['ip'] ?? null,
            userAgent: $payload['user_agent'] ?? null,
            source: (string) ($payload['source'] ?? 'external'),
            grantDoor: (bool) ($payload['grant_door'] ?? false),
            denyDoor: (bool) ($payload['deny_door'] ?? false),
            meta: is_array($payload['meta'] ?? null) ? $payload['meta'] : [],
        );
    }
}
