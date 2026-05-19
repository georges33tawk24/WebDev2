<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class LiveUpdateService
{
    private const CACHE_TTL_SECONDS = 3600;

    public function bump(User|int $user): void
    {
        $userId = $user instanceof User ? $user->id : $user;

        Cache::put(
            $this->cursorKey($userId),
            (int) floor(microtime(true) * 1000),
            self::CACHE_TTL_SECONDS,
        );
    }

    public function bumpMany(iterable $users): void
    {
        foreach ($users as $user) {
            if ($user instanceof User || is_int($user)) {
                $this->bump($user);
            }
        }
    }

    public function cursorFor(int $userId): int
    {
        return (int) Cache::get($this->cursorKey($userId), 0);
    }

    private function cursorKey(int $userId): string
    {
        return "live:cursor:{$userId}";
    }
}
