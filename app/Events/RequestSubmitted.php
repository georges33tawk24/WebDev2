<?php

namespace App\Events;

use App\Models\ServiceRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ServiceRequest $serviceRequest)
    {
        $this->serviceRequest->load(['citizen', 'service', 'office']);
    }

    public function broadcastOn(): Channel
    {
        return new Channel('office.' . $this->serviceRequest->office_id);
    }

    public function broadcastAs(): string
    {
        return 'request.submitted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->serviceRequest->id,
            'reference_number' => $this->serviceRequest->reference_number,
            'citizen_name' => $this->serviceRequest->citizen->name ?? 'Citizen',
            'service_name' => $this->serviceRequest->service->name ?? 'Service',
            'office_name' => $this->serviceRequest->office->name ?? 'Office',
            'status' => $this->serviceRequest->status,
            'submitted_at' => optional($this->serviceRequest->submitted_at)->format('d M Y - h:i A'),
            'url' => route('staff.requests.show', $this->serviceRequest),
        ];
    }
}