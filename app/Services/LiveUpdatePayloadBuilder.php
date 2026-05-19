<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\User;

class LiveUpdatePayloadBuilder
{
    public function build(User $user): array
    {
        $payload = [
            'notifications' => $this->notificationsPayload($user),
        ];

        if ($user->isCitizen()) {
            $payload['requests'] = $this->citizenRequestsPayload($user);
        }

        if ($user->role?->slug === 'office_staff' && $user->office_id) {
            $payload['staff_requests'] = $this->staffRequestsPayload((int) $user->office_id);
        }

        return $payload;
    }

    /**
     * @return array{unread_count: int, notifications: list<array<string, mixed>>}
     */
    private function notificationsPayload(User $user): array
    {
        $unreadCount = Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $items = Notification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn (Notification $notification) => [
                'id' => $notification->id,
                'title' => $notification->localizedTitle(),
                'body' => $notification->localizedBody(),
                'read' => $notification->read_at !== null,
                'created_at' => localized_datetime($notification->created_at),
            ])
            ->values()
            ->all();

        return [
            'unread_count' => $unreadCount,
            'notifications' => $items,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function citizenRequestsPayload(User $user): array
    {
        return ServiceRequest::query()
            ->with('payments')
            ->where('citizen_id', $user->id)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (ServiceRequest $request) => [
                'id' => $request->id,
                'status' => $request->status,
                'status_label' => __('ui.status.'.$request->status),
                'is_paid' => $request->isPaid(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function staffRequestsPayload(int $officeId): array
    {
        return ServiceRequest::query()
            ->where('office_id', $officeId)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (ServiceRequest $request) => [
                'id' => $request->id,
                'status' => $request->status,
                'status_label' => __('ui.status.'.$request->status),
            ])
            ->values()
            ->all();
    }
}
