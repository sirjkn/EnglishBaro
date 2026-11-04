<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = SupportTicket::query()->with('user');

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        $tickets = $query->latest('last_reply_at')->latest()->paginate(20)->withQueryString();

        $overview = [
            'waiting' => SupportTicket::query()->where('status', SupportTicket::WAITING_FOR_ANSWER)->count(),
            'answered' => SupportTicket::query()->where('status', SupportTicket::ANSWERED)->count(),
            'closed' => SupportTicket::query()->where('status', SupportTicket::CLOSED)->count(),
        ];

        return view('admin.support.index', [
            'tickets' => $tickets,
            'overview' => $overview,
            'filters' => $request->only('status'),
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        $this->authorize('viewAny', User::class);

        $ticket->load(['replies.user', 'user.studentProfile']);

        return view('admin.support.show', ['ticket' => $ticket]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'is_admin_reply' => true,
            'message' => $validated['message'],
        ]);

        $ticket->update(['status' => SupportTicket::ANSWERED, 'last_reply_at' => now()]);

        AppNotification::create([
            'user_id' => $ticket->user_id,
            'type' => 'support_ticket_reply',
            'title' => 'Support replied: '.$ticket->subject,
            'body' => Str::limit($validated['message'], 140),
            'data' => ['url' => route('student.support.show', $ticket)],
        ]);

        AuditLogger::log('admin.support_ticket.replied', $ticket, [], ['message' => $validated['message']]);

        return back()->with('status', 'Reply sent.');
    }

    public function close(SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $ticket->update(['status' => SupportTicket::CLOSED]);

        AppNotification::create([
            'user_id' => $ticket->user_id,
            'type' => 'support_ticket_closed',
            'title' => 'Support ticket closed: '.$ticket->subject,
            'body' => null,
            'data' => ['url' => route('student.support.show', $ticket)],
        ]);

        AuditLogger::log('admin.support_ticket.closed', $ticket, [], ['status' => SupportTicket::CLOSED]);

        return back()->with('status', 'Ticket closed.');
    }

    public function reopen(SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $ticket->update(['status' => SupportTicket::WAITING_FOR_ANSWER]);

        AuditLogger::log('admin.support_ticket.reopened', $ticket, [], ['status' => SupportTicket::WAITING_FOR_ANSWER]);

        return back()->with('status', 'Ticket reopened.');
    }
}
