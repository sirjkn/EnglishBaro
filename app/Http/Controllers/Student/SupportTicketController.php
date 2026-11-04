<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(): View
    {
        $tickets = Auth::user()->supportTickets()->latest('last_reply_at')->latest()->get();

        return view('student.support.index', ['tickets' => $tickets]);
    }

    public function create(): View
    {
        return view('student.support.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $user = Auth::user();

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => $validated['subject'],
            'status' => SupportTicket::WAITING_FOR_ANSWER,
            'last_reply_at' => now(),
        ]);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_admin_reply' => false,
            'message' => $validated['message'],
        ]);

        return redirect()->route('student.support.show', $ticket)->with('status', 'Support ticket submitted.');
    }

    public function show(SupportTicket $ticket): View
    {
        abort_unless($ticket->user_id === Auth::id(), 404);

        $ticket->load('replies.user');

        return view('student.support.show', ['ticket' => $ticket]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_unless($ticket->user_id === Auth::id(), 404);
        abort_if($ticket->isClosed(), 403, 'This ticket is closed. Open a new ticket if you still need help.');

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'is_admin_reply' => false,
            'message' => $validated['message'],
        ]);

        $ticket->update(['status' => SupportTicket::WAITING_FOR_ANSWER, 'last_reply_at' => now()]);

        return back()->with('status', 'Reply sent.');
    }

    public function close(SupportTicket $ticket): RedirectResponse
    {
        abort_unless($ticket->user_id === Auth::id(), 404);

        $ticket->update(['status' => SupportTicket::CLOSED]);

        return back()->with('status', 'Ticket closed.');
    }
}
