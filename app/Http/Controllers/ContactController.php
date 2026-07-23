<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Public/Contact', $this->sharedProps());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'phone'   => 'nullable|string|max:30',
            'subject' => 'required|string|max:100',
            'message' => 'required|string|min:20|max:2000',
            'rgpd'    => 'accepted',
        ]);

        // Envoi email à l'équipe IBIG
        try {
            Mail::raw(
                "Nouveau message de contact FactPro\n\n" .
                "Nom: {$data['name']}\nEmail: {$data['email']}\nTél: {$data['phone']}\n" .
                "Sujet: {$data['subject']}\n\nMessage:\n{$data['message']}",
                fn ($m) => $m->to('support@ibigsoft.com')
                             ->subject("[FactPro Contact] {$data['subject']} — {$data['name']}")
                             ->replyTo($data['email'], $data['name'])
            );
        } catch (\Throwable) {
            // Ne pas bloquer si l'email échoue
        }

        return back()->with('contact_success', true);
    }

    private function sharedProps(): array
    {
        return [
            'canLogin'    => \Illuminate\Support\Facades\Route::has('login'),
            'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
        ];
    }
}
