<?php
namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class SupportTicketController extends Controller
{
    public function index(Request $request): Response {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->with('replies')
            ->latest()
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id, 'ticket_number' => $t->ticket_number,
                'subject' => $t->subject, 'category' => $t->category,
                'priority' => $t->priority, 'status' => $t->status,
                'replies_count' => $t->replies->count(),
                'created_at' => $t->created_at->diffForHumans(),
            ]);
        return Inertia::render('Support/Index', ['tickets' => $tickets]);
    }

    public function create(): Response {
        return Inertia::render('Support/Create');
    }

    public function store(Request $request): RedirectResponse {
        $data = $request->validate([
            'subject'       => 'required|string|max:150',
            'category'      => 'required|in:general,billing,technical,feature,other',
            'priority'      => 'required|in:low,normal,high,urgent',
            'first_message' => 'required|string|min:20|max:5000',
        ]);
        $data['user_id'] = $request->user()->id;
        $ticket = SupportTicket::create($data);

        // Email confirmation client
        try {
            Mail::raw(
                "Votre ticket #{$ticket->ticket_number} a été créé.\n\nSujet : {$ticket->subject}\n\nNous vous répondrons dans les meilleurs délais.\n\nÉquipe IBIG FactPro",
                fn ($m) => $m->to($request->user()->email)
                             ->subject("[FactPro Support] Ticket #{$ticket->ticket_number} créé")
            );
            // Email admin
            Mail::raw(
                "Nouveau ticket #{$ticket->ticket_number}\nDe : {$request->user()->name} ({$request->user()->email})\nSujet : {$ticket->subject}\nCatégorie : {$ticket->category}\nPriorité : {$ticket->priority}\n\n{$ticket->first_message}",
                fn ($m) => $m->to('support@ibigsoft.com')
                             ->subject("[FactPro] Nouveau ticket {$ticket->ticket_number} — {$ticket->priority}")
            );
        } catch (\Throwable) {}

        return redirect()->route('support.show', $ticket)->with('success', "Ticket #{$ticket->ticket_number} créé !");
    }

    public function show(Request $request, SupportTicket $supportTicket): Response {
        abort_unless($supportTicket->user_id === $request->user()->id, 403);
        $ticket = [
            'id' => $supportTicket->id, 'ticket_number' => $supportTicket->ticket_number,
            'subject' => $supportTicket->subject, 'category' => $supportTicket->category,
            'priority' => $supportTicket->priority, 'status' => $supportTicket->status,
            'first_message' => $supportTicket->first_message,
            'created_at' => $supportTicket->created_at->format('d/m/Y H:i'),
            'replies' => $supportTicket->replies->map(fn ($r) => [
                'id' => $r->id, 'message' => $r->message, 'is_staff' => $r->is_staff,
                'user' => $r->user?->name, 'created_at' => $r->created_at->format('d/m/Y H:i'),
            ]),
        ];
        return Inertia::render('Support/Show', ['ticket' => $ticket]);
    }

    public function reply(Request $request, SupportTicket $supportTicket): RedirectResponse {
        abort_unless($supportTicket->user_id === $request->user()->id, 403);
        $request->validate(['message' => 'required|string|min:5|max:5000']);
        SupportTicketReply::create([
            'ticket_id' => $supportTicket->id,
            'user_id'   => $request->user()->id,
            'message'   => $request->message,
            'is_staff'  => false,
        ]);
        $supportTicket->update(['status' => 'open']);
        return back()->with('success', 'Réponse envoyée.');
    }
}
