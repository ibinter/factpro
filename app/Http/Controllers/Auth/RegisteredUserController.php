<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Inscription : crée l'utilisateur + sa société + démarre l'essai gratuit 7 jours.
     *
     * @throws ValidationException
     */
    public function store(Request $request, LicenseService $licenses): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'country' => 'nullable|string|size:2',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request, $licenses) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'country' => strtoupper($request->country ?? 'CI'),
                'password' => Hash::make($request->password),
            ]);

            $company = Company::create([
                'owner_id' => $user->id,
                'name' => $request->company_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'country' => strtoupper($request->country ?? 'CI'),
                'currency' => 'XOF',
            ]);

            $company->users()->attach($user->id, ['role' => 'owner']);
            $user->forceFill(['current_company_id' => $company->id])->save();

            // Essai gratuit 7 jours (cahier des charges §1.3 / script §13)
            $licenses->startTrial($user);

            // Attacher les UTM capturés en session
            $utmFields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'referrer_url'];
            $utmData = array_filter(array_intersect_key(session()->all(), array_flip($utmFields)));
            if (!empty($utmData)) {
                $user->update($utmData);
                foreach ($utmFields as $field) {
                    session()->forget($field);
                }
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        // Programme ambassadeur : enregistre le parrainage si ?ref= présent
        if ($request->has('ref')) {
            app(\App\Services\ReferralService::class)->registerReferral($user, $request->input('ref'));
        }

        return redirect(route('dashboard', absolute: false));
    }
}
