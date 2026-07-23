<?php
namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class PartnersController extends Controller {
    public function index(): Response {
        return Inertia::render('Public/Partners', [
            'canLogin'    => \Route::has('login'),
            'canRegister' => \Route::has('register'),
        ]);
    }

    public function apply(Request $request): RedirectResponse {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'email'        => 'required|email',
            'company'      => 'required|string|max:150',
            'country'      => 'required|string|max:100',
            'type'         => 'required|in:integrator,reseller,consultant,agency',
            'clients_count' => 'nullable|integer|min:0',
            'message'      => 'nullable|string|max:1000',
        ]);

        try {
            Mail::raw(
                "Nouvelle candidature partenaire FactPro\n\n".
                "Nom : {$data['name']}\n".
                "Email : {$data['email']}\n".
                "Société : {$data['company']}\n".
                "Pays : {$data['country']}\n".
                "Type : {$data['type']}\n".
                "Nombre de clients : ".($data['clients_count'] ?? 'Non précisé')."\n\n".
                "Message : ".($data['message'] ?? 'Aucun'),
                fn($m) => $m->to('partenaires@ibigsoft.com')
                             ->subject("[FactPro Partenaires] Candidature — {$data['company']} ({$data['country']})")
            );
            Mail::raw(
                "Bonjour {$data['name']},\n\n".
                "Merci pour votre intérêt à devenir partenaire IBIG FactPro !\n\n".
                "Nous avons bien reçu votre candidature et notre équipe partenaires vous contactera dans les 48 heures.\n\n".
                "En attendant, n'hésitez pas à explorer notre programme partenaires sur factpro.ibigsoft.com/partenaires\n\n".
                "Cordialement,\nL'équipe IBIG Soft",
                fn($m) => $m->to($data['email'])
                             ->subject("[FactPro] Candidature partenaire reçue ✓")
            );
        } catch (\Throwable) {}

        return back()->with('partner_success', true);
    }
}
