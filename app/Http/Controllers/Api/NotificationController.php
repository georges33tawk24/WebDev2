<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

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
                'data' => $notification->data ?? [],
            ]);

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $items,
        ]);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 403);

        $notification->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
