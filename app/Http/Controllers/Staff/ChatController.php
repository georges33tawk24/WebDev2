<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\ServiceRequest;
use App\Services\ChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService,
    ) {}

    public function index(): View
    {
        $officeId = auth()->user()->office_id;

        abort_unless($officeId, 403);

        $requests = ServiceRequest::query()
            ->with(['citizen', 'service'])
            ->where('office_id', $officeId)
            ->whereHas('messages')
            ->withCount([
                'messages as unread_count' => function ($query) {
                    $query->where('recipient_id', auth()->id())->whereNull('read_at');
                },
            ])
            ->withMax('messages as last_message_at', 'created_at')
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('staff.chats.index', compact('requests'));
    }

    public function show(ServiceRequest $serviceRequest): View
    {
        $this->chatService->authorizeView($serviceRequest, auth()->user());
        $this->chatService->markReadForUser($serviceRequest, auth()->user());

        $serviceRequest->load(['citizen', 'service', 'office']);

        $messages = Message::with(['sender'])
            ->where('service_request_id', $serviceRequest->id)
            ->orderBy('created_at')
            ->get();

        return view('staff.chats.show', compact('serviceRequest', 'messages'));
    }

    public function sendMessage(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $this->chatService->send($serviceRequest, $request->user(), $validated['message']);

        return back()->with('success', __('ui.flash.message_sent'));
    }
}
