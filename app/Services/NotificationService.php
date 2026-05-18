<?php

namespace App\Services;

use App\Models\UserNotification;

class NotificationService
{
    public static function send(
        int $userId,
        string $title,
        string $message,
        string $type = 'general',
        ?string $url = null
    ): void {

        UserNotification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'url' => $url,
            'is_read' => false,
        ]);
    }
}