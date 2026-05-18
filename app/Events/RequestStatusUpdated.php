<?php

namespace App\Events;

use App\Models\ServiceRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public string $oldStatus,
        public string $newStatus
    ) {
        $this->serviceRequest->load(['service', 'office', 'citizen']);
    }

    public function broadcastOn(): Channel
    {
        return new Channel('citizen.' . $this->serviceRequest->citizen_id);
    }

    public function broadcastAs(): string
    {
        return 'request.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->serviceRequest->id,
            'reference_number' => $this->serviceRequest->reference_number,
            'service_name' => $this->serviceRequest->service->name ?? 'Service',
            'office_name' => $this->serviceRequest->office->name ?? 'Office',
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => 'Your request status changed from '
                . ucwords(str_replace('_', ' ', $this->oldStatus))
                . ' to '
                . ucwords(str_replace('_', ' ', $this->newStatus))
                . '.',
            'url' => route('citizen.requests'),
        ];
    }
}