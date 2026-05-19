<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LiveUpdatePayloadBuilder;
use App\Services\LiveUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LiveUpdateController extends Controller
{
    public function stream(Request $request, LiveUpdateService $live, LiveUpdatePayloadBuilder $builder): StreamedResponse
    {
        $user = $request->user();
        $userId = (int) $user->id;
        $lastCursor = (int) $request->query('cursor', 0);

        return response()->stream(function () use ($user, $userId, $lastCursor, $live, $builder): void {
            @set_time_limit(0);

            for ($tick = 0; $tick < 12; $tick++) {
                $cursor = $live->cursorFor($userId);

                if ($cursor > $lastCursor) {
                    $payload = array_merge(
                        ['cursor' => $cursor],
                        $builder->build($user),
                    );

                    echo "event: update\n";
                    echo 'data: '.json_encode($payload, JSON_THROW_ON_ERROR)."\n\n";

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }

                    flush();
                    $lastCursor = $cursor;
                } else {
                    echo ": keepalive\n\n";

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }

                    flush();
                }

                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function snapshot(Request $request, LiveUpdatePayloadBuilder $builder, LiveUpdateService $live): JsonResponse
    {
        $user = $request->user();

        return response()->json(array_merge(
            ['cursor' => $live->cursorFor((int) $user->id)],
            $builder->build($user),
        ));
    }
}
