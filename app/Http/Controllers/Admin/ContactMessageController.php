<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactMessage::query();

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        $messages = $query->latest()->paginate(20)->withQueryString();

        return view('admin.contact-messages.index', [
            'messages' => $messages,
            'filters' => $request->only('status'),
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }

        return view('admin.contact-messages.show', ['message' => $contactMessage]);
    }

    public function update(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,read,replied,archived'],
        ]);

        $contactMessage->update($validated);

        AuditLogger::log('admin.contact_message.updated', $contactMessage, [], $validated);

        return back()->with('status', 'Message updated.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        AuditLogger::log('admin.contact_message.deleted', $contactMessage, $contactMessage->toArray());

        $contactMessage->delete();

        return redirect()->route('admin.contact-messages.index')->with('status', 'Message deleted.');
    }
}
