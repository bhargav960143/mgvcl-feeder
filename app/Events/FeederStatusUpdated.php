<?php

namespace App\Events;

use App\Models\Feeder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class FeederStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly Feeder $feeder,
        public readonly string $oldStatus,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('feeders');
    }

    public function broadcastWith(): array
    {
        $divisionId = $this->feeder->substation?->subDivision?->division_id;

        return [
            'feeder_id'       => $this->feeder->id,
            'old_status'      => $this->oldStatus,
            'new_status'      => $this->feeder->current_status,
            'division_id'     => $divisionId,
            'updated_by'      => $this->feeder->lastUpdatedBy?->name ?? '—',
            'last_updated_at' => $this->feeder->last_updated_at?->diffForHumans() ?? '—',
        ];
    }
}
