<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Multi-sociétés (cahier IBIG §3 MLT) : gérer N entreprises depuis 1 compte.
 * Liste, création (limitée par le forfait), bascule et paramètres de la
 * société courante (logo inclus).
 */
class CompanyController extends Controller
{
    /** Page « Mes sociétés » : liste + création limitée par le forfait. */
    public function index(Request $request, LicenseService $licenses): Response
    {
        $user = $request->user();
        $companies = $this->companiesFor($user);

        return Inertia::render('Companies/Index', [
            'companies' => $companies->map(fn (Company $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'city' => $c->city,
                'country' => $c->country,
                'currency' => $c->currency,
                'logo_path' => $c->logo_path,
                'role' => $c->pivot->role ?? ($c->owner_id === $user->id ? 'owner' : 'member'),
                'is_current' => $c->id === $user->current_company_id,
                'customers_count' => $c->customers_count,
                'documents_count' => $c->documents_count,
            ])->values(),
            'canCreate' => ! $licenses->limitReached($user, 'companies', $companies->count()),
            'limit' => $licenses->currentFor($user)?->limit('companies'),
        ]);
    }

    /** Crée une nouvelle société (gate limite forfait) et bascule dessus. */
    public function store(Request $request, LicenseService $licenses): RedirectResponse
    {
        $user = $request->user();
        $count = $this->companiesFor($user)->count();

        if ($licenses->limitReached($user, 'companies', $count)) {
            $limit = $licenses->currentFor($user)?->limit('companies') ?? $count;

            return back()->with('error', "Limite de sociétés atteinte pour votre forfait ({$limit}). Passez au forfait supérieur.");
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'tax_id' => 'nullable|string|max:50',
        ]);

        $company = Company::create([
            ...$data,
            'country' => strtoupper($data['country']),
            'currency' => strtoupper($data['currency']),
            'owner_id' => $user->id,
        ]);

        $company->users()->attach($user->id, ['role' => 'owner']);
        $user->forceFill(['current_company_id' => $company->id])->save();

        return redirect()->route('companies.index')
            ->with('success', "Société « {$company->name} » créée et activée.");
    }

    /** Bascule la société courante (membre pivot ou propriétaire uniquement). */
    public function switch(Request $request, Company $company): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $company->owner_id === $user->id
                || $company->users()->whereKey($user->id)->exists(),
            403
        );

        $user->forceFill(['current_company_id' => $company->id])->save();

        return redirect()->route('dashboard')
            ->with('success', "Société active : {$company->name}");
    }

    /** Paramètres de la société courante (identité, coordonnées, facturation, logo). */
    public function settings(Request $request): Response
    {
        $company = $request->user()->currentCompany;
        abort_unless($company, 404);

        return Inertia::render('Companies/Settings', [
            'company' => $company->only([
                'id', 'name', 'legal_name', 'email', 'phone', 'address', 'city',
                'country', 'currency', 'tax_id', 'trade_register', 'logo_path',
                'invoice_footer', 'default_template', 'default_tax_rate',
                'signature_path', 'stamp_path', 'signature_label', 'show_signature', 'show_stamp',
            ]),
            'templates' => collect(config('pdf_templates'))
                ->map(fn ($t, $key) => ['key' => $key, 'name' => $t['name'], 'family' => $t['family']])
                ->values(),
        ]);
    }

    /** Met à jour les paramètres de la société courante (owner/admin seulement). */
    public function updateSettings(Request $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user->currentCompany;
        abort_unless($company, 404);
        $this->authorizeManage($user, $company);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
            'tax_id' => 'nullable|string|max:50',
            'trade_register' => 'nullable|string|max:100',
            'invoice_footer' => 'nullable|string|max:500',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'default_template' => ['nullable', Rule::in(array_keys(config('pdf_templates')))],
        ]);

        $company->update([
            ...$data,
            'country' => strtoupper($data['country']),
            'currency' => strtoupper($data['currency']),
        ]);

        return redirect()->route('companies.index')->with('success', 'Paramètres de la société enregistrés.');
    }

    /** Upload du logo de la société courante (disk public, dossier companies/). */
    public function uploadLogo(Request $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user->currentCompany;
        abort_unless($company, 404);
        $this->authorizeManage($user, $company);

        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);

        if ($company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $path = $request->file('logo')->store('companies', 'public');
        $company->update(['logo_path' => $path]);

        return redirect()->route('companies.index')->with('success', 'Logo de la société mis à jour.');
    }

    /** Upload de la signature numérique de l'entreprise (image PNG/JPG). */
    public function uploadSignature(Request $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user->currentCompany;
        abort_unless($company, 404);
        $this->authorizeManage($user, $company);

        $request->validate([
            'signature' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($company->signature_path) {
            Storage::disk('public')->delete($company->signature_path);
        }

        $path = $request->file('signature')->store('companies/signatures', 'public');
        $company->update(['signature_path' => $path]);

        return redirect()->route('companies.index')->with('success', 'Signature mise à jour.');
    }

    /** Upload du cachet/tampon de l'entreprise (image PNG/JPG transparente recommandée). */
    public function uploadStamp(Request $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user->currentCompany;
        abort_unless($company, 404);
        $this->authorizeManage($user, $company);

        $request->validate([
            'stamp' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($company->stamp_path) {
            Storage::disk('public')->delete($company->stamp_path);
        }

        $path = $request->file('stamp')->store('companies/stamps', 'public');
        $company->update(['stamp_path' => $path]);

        return redirect()->route('companies.index')->with('success', 'Cachet mis à jour.');
    }

    /** Active/désactive signature et cachet, met à jour le libellé signataire. */
    public function updateSignatureSettings(Request $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user->currentCompany;
        abort_unless($company, 404);
        $this->authorizeManage($user, $company);

        $data = $request->validate([
            'show_signature'   => 'boolean',
            'show_stamp'       => 'boolean',
            'signature_label'  => 'nullable|string|max:100',
        ]);

        $company->update($data);

        return redirect()->route('companies.index')->with('success', 'Paramètres de signature enregistrés.');
    }

    /**
     * Sociétés du user : pivot company_user + sociétés possédées non pivotées
     * (fusion sans doublon), avec compteurs clients/documents.
     */
    private function companiesFor(User $user): Collection
    {
        $pivoted = $user->companies()->withCount(['customers', 'documents'])->get();
        $owned = $user->ownedCompanies()->withCount(['customers', 'documents'])->get();

        return $pivoted
            ->concat($owned->reject(fn (Company $c) => $pivoted->contains('id', $c->id)))
            ->values();
    }

    /** Owner (colonne owner_id) ou rôle pivot owner/admin — sinon 403. */
    private function authorizeManage(User $user, Company $company): void
    {
        if ($company->owner_id === $user->id) {
            return;
        }

        $role = $company->users()->whereKey($user->id)->first()?->pivot?->role;

        abort_unless(in_array($role, ['owner', 'admin'], true), 403);
    }
}
