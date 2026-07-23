<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class SupportAdminController extends Controller {
    public function index(): Response {
        $tickets = SupportTicket::with('user:id,name,email')
            ->latest()->get()
            ->map(fn ($t) => [
                'id' => $t->id, 'ticket_number' => $t->ticket_number,
                'subject' => $t->subject, 'category' => $t->category,
                'priority' => $t->priority, 'status' => $t->status,
                'user' => $t->user?->name, 'email' => $t->user?->email,
                'replies_count' => $t->replies()->count(),
                'created_at' => $t->created_at->format('d/m/Y H:i'),
            ]);
        $stats = [
            'open'        => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved'    => SupportTicket::where('status', 'resolved')->count(),
        ];
        return Inertia::render('Admin/Support', ['tickets' => $tickets, 'stats' => $stats]);
    }

    public function show(SupportTicket $ticket): Response {
        $ticket->load(['user:id,name,email', 'replies.user:id,name']);
        return Inertia::render('Admin/SupportShow', [
            'ticket' => [
                'id' => $ticket->id, 'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject, 'category' => $ticket->category,
                'priority' => $ticket->priority, 'status' => $ticket->status,
                'first_message' => $ticket->first_message,
                'user' => $ticket->user?->name, 'email' => $ticket->user?->email,
                'created_at' => $ticket->created_at->format('d/m/Y H:i'),
                'replies' => $ticket->replies->map(fn ($r) => [
                    'id' => $r->id, 'message' => $r->message, 'is_staff' => $r->is_staff,
                    'user' => $r->user?->name, 'created_at' => $r->created_at->format('d/m/Y H:i'),
                ]),
            ],
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse {
        $request->validate(['message' => 'required|string|min:5|max:5000', 'status' => 'required|in:open,in_progress,waiting_user,resolved,closed']);
        SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $request->user()->id,
            'message'   => $request->message,
            'is_staff'  => true,
        ]);
        $ticket->update(['status' => $request->status]);

        // Email notif client
        try {
            Mail::raw(
                "L'équipe IBIG a répondu à votre ticket #{$ticket->ticket_number}.\n\n".
                "Message : {$request->message}\n\nConsultez votre ticket : ".url("/support/{$ticket->id}"),
                fn ($m) => $m->to($ticket->user->email)
                             ->subject("[FactPro Support] Réponse à votre ticket #{$ticket->ticket_number}")
            );
        } catch (\Throwable) {}

        return back()->with('success', 'Réponse envoyée au client.');
    }
}
