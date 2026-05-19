<?php

namespace App\Services;

use App\Models\Message;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ChatService
{
    public function authorizeView(ServiceRequest $serviceRequest, User $user): void
    {
        $role = $user->role?->slug;

        if ($role === 'citizen') {
            abort_if((int) $serviceRequest->citizen_id !== (int) $user->id, 403);

            return;
        }

        if ($role === 'office_staff') {
            abort_unless(
                $user->office_id && (int) $serviceRequest->office_id === (int) $user->office_id,
                404
            );

            return;
        }

        abort(403);
    }

    public function staffRecipientFor(ServiceRequest $serviceRequest): ?User
    {
        return User::query()
            ->where('office_id', $serviceRequest->office_id)
            ->whereHas('role', fn ($query) => $query->where('slug', 'office_staff'))
            ->first();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function messagesSince(ServiceRequest $serviceRequest, int $afterId = 0): Collection
    {
        return Message::query()
            ->with('sender:id,name')
            ->where('service_request_id', $serviceRequest->id)
            ->when($afterId > 0, fn ($query) => $query->where('id', '>', $afterId))
            ->orderBy('id')
            ->get()
            ->map(fn (Message $message) => $this->formatMessage($message));
    }

    public function markReadForUser(ServiceRequest $serviceRequest, User $user): void
    {
        Message::query()
            ->where('service_request_id', $serviceRequest->id)
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function send(ServiceRequest $serviceRequest, User $sender, string $body): Message
    {
        $this->authorizeView($serviceRequest, $sender);

        $recipient = $this->resolveRecipient($serviceRequest, $sender);

        if (! $recipient) {
            throw ValidationException::withMessages([
                'message' => [__('ui.flash.no_staff_for_chat')],
            ]);
        }

        $message = Message::query()->create([
            'service_request_id' => $serviceRequest->id,
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'message' => $body,
        ]);

        $message->load('sender:id,name');

        $notifications = app(NotificationService::class);

        if ($sender->role?->slug === 'citizen') {
            foreach ($notifications->officeStaffFor((int) $serviceRequest->office_id) as $staff) {
                $notifications->newChatMessage($serviceRequest, $sender, $staff);
            }
        } else {
            $notifications->newChatMessage($serviceRequest, $sender, $recipient);
        }

        return $message;
    }

    private function resolveRecipient(ServiceRequest $serviceRequest, User $sender): ?User
    {
        if ($sender->role?->slug === 'citizen') {
            return $this->staffRecipientFor($serviceRequest);
        }

        return $serviceRequest->citizen;
    }

    /**
     * @return array<string, mixed>
     */
    public function formatMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'message' => $message->message,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender?->name ?? __('ui.na'),
            'is_mine' => (int) $message->sender_id === (int) auth()->id(),
            'created_at' => $message->created_at
                ? localized_datetime($message->created_at)
                : __('ui.na'),
            'created_at_iso' => $message->created_at?->toIso8601String(),
        ];
    }
}
