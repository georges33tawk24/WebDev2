<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedback = Feedback::with(['citizen', 'office', 'serviceRequest'])
            ->where('office_id', auth()->user()->office_id)
            ->latest()
            ->paginate(10);

        return view('staff.feedback.index', compact('feedback'));
    }

    public function reply(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'reply_type' => ['required', 'in:public,private'],
            'reply'      => ['required', 'string', 'max:1000'],
        ]);

        if ($validated['reply_type'] === 'public') {
            $feedback->update(['public_reply' => $validated['reply']]);
        } else {
            $feedback->update(['private_reply' => $validated['reply']]);
        }

        return back()->with('success', __('ui.flash.reply_sent'));
    }
}