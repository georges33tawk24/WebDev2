<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
        $this->message->load(['sender', 'serviceRequest.service']);
    }

    public function broadcastOn(): Channel
    {
        return new Channel('request-chat.' . $this->message->service_request_id);
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'service_request_id' => $this->message->service_request_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name ?? 'User',
            'message' => $this->message->message,
            'created_at' => $this->message->created_at->format('d M Y - h:i A'),
            'service_name' => $this->message->serviceRequest->service->name ?? 'Service',
        ];
    }
}