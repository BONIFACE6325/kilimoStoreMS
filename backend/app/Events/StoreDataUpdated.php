<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreDataUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tenantId;
    public string $entityType;
    public string $action;
    public array $payload;

    public function __construct(string $tenantId, string $entityType, string $action = 'updated', array $payload = [])
    {
        $this->tenantId = $tenantId;
        $this->entityType = $entityType;
        $this->action = $action;
        $this->payload = $payload;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('tenant.' . $this->tenantId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'store.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'entity_type' => $this->entityType,
            'action' => $this->action,
            'payload' => $this->payload,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
