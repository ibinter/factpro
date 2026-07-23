<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class DemoController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Demo', [
            'canLogin'    => \Route::has('login'),
            'canRegister' => \Route::has('register'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'email'        => 'required|email',
            'company'      => 'required|string|max:150',
            'phone'        => 'nullable|string|max:30',
            'country'      => 'required|string|max:100',
            'company_size' => 'required|in:1-5,6-20,21-50,51-200,200+',
            'sector'       => 'nullable|string|max:100',
            'slot'         => 'required|in:morning,afternoon,evening,flexible',
            'message'      => 'nullable|string|max:500',
        ]);

        $slots = [
            'morning'   => 'Matin (9h-12h)',
            'afternoon' => 'Après-midi (14h-17h)',
            'evening'   => 'Soir (17h-19h)',
            'flexible'  => "Flexible (n'importe quand)",
        ];

        try {
            Mail::raw(
                "Nouvelle demande de démo FactPro\n\n".
                "Nom : {$data['name']}\n".
                "Email : {$data['email']}\n".
                "Téléphone : ".($data['phone'] ?? 'Non renseigné')."\n".
                "Société : {$data['company']}\n".
                "Pays : {$data['country']}\n".
                "Taille entreprise : {$data['company_size']} employés\n".
                "Secteur : ".($data['sector'] ?? 'Non précisé')."\n".
                "Créneau préféré : ".$slots[$data['slot']]."\n\n".
                "Message : ".($data['message'] ?? 'Aucun'),
                fn ($m) => $m->to('demo@ibigsoft.com')
                             ->subject("[FactPro Demo] {$data['company']} — {$data['country']} — {$data['company_size']} emp.")
            );
            Mail::raw(
                "Bonjour {$data['name']},\n\n".
                "Merci pour votre demande de démo IBIG FactPro !\n\n".
                "Notre équipe commerciale vous contactera dans les 24 heures pour planifier votre démonstration personnalisée.\n\n".
                "Créneau préféré : ".$slots[$data['slot']]."\n\n".
                "En attendant, vous pouvez :\n".
                "- Démarrer votre essai gratuit de 14 jours : https://factpro.ibigsoft.com/register\n".
                "- Consulter nos tarifs : https://factpro.ibigsoft.com/pricing\n".
                "- Lire notre blog : https://factpro.ibigsoft.com/blog\n\n".
                "À très bientôt,\nL'équipe IBIG Soft",
                fn ($m) => $m->to($data['email'])
                             ->subject('[FactPro] Votre démo est en cours de planification ✓')
            );
        } catch (\Throwable) {}

        return back()->with('demo_success', true);
    }

    public function login()
    {
        $user = User::where('email', 'demo@factpro.test')->first();

        if (! $user) {
            return redirect('/')->with('error', 'Compte démo non disponible. Contactez-nous.');
        }

        Auth::login($user, remember: false);

        session()->regenerate();

        return redirect()->route('dashboard');
    }
}
