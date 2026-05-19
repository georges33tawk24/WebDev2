<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService,
    ) {}

    public function index(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $user = $request->user();
        $this->chatService->authorizeView($serviceRequest, $user);

        if ($request->boolean('mark_read', true)) {
            $this->chatService->markReadForUser($serviceRequest, $user);
        }

        $afterId = $request->integer('after_id', 0);

        return response()->json([
            'messages' => $this->chatService->messagesSince($serviceRequest, $afterId)->values(),
        ]);
    }

    public function store(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = $this->chatService->send(
            $serviceRequest,
            $request->user(),
            $validated['message'],
        );

        return response()->json([
            'message' => $this->chatService->formatMessage($message),
        ], 201);
    }
}
