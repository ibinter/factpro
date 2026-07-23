<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\SupplierPortalToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SupplierPortalController extends Controller
{
    /** Envoyer un lien portail à un fournisseur */
    public function invite(Request $request, Document $document)
    {
        $company = $request->user()->currentCompany;
        if ($document->company_id !== $company->id) {
            abort(403);
        }

        $data = $request->validate([
            'supplier_name'  => 'required|string|max:255',
            'supplier_email' => 'required|email',
        ]);

        $token = SupplierPortalToken::create([
            'company_id'     => $company->id,
            'document_id'    => $document->id,
            'supplier_name'  => $data['supplier_name'],
            'supplier_email' => $data['supplier_email'],
            'token'          => Str::random(48),
            'expires_at'     => now()->addDays(14),
        ]);

        try {
            \Mail::to($token->supplier_email)->send(new \App\Mail\SupplierInviteMail($token));
        } catch (\Exception) {}

        return back()->with('success', "Invitation envoyée à {$token->supplier_email}.");
    }

    /** Page publique vue par le fournisseur */
    public function show(string $token): Response|\Illuminate\Http\RedirectResponse
    {
        $record = SupplierPortalToken::with(['document.lines', 'company'])
            ->where('token', $token)
            ->firstOrFail();

        if ($record->isExpired()) {
            return Inertia::render('SupplierPortal/Expired');
        }

        if (! $record->viewed_at) {
            $record->update(['viewed_at' => now(), 'status' => 'viewed']);
        }

        return Inertia::render('SupplierPortal/Show', [
            'record'   => $record,
            'document' => $record->document->load('lines'),
            'company'  => $record->company->only(['name', 'address', 'email', 'phone', 'logo']),
            'token'    => $token,
        ]);
    }

    /** Le fournisseur soumet son offre */
    public function respond(Request $request, string $token)
    {
        $record = SupplierPortalToken::where('token', $token)->firstOrFail();
        if ($record->isExpired()) {
            abort(410, 'Lien expiré.');
        }

        $data = $request->validate([
            'quoted_price'   => 'required|numeric|min:0',
            'delivery_days'  => 'required|integer|min:1',
            'supplier_notes' => 'nullable|string|max:2000',
        ]);

        $record->update($data + ['responded_at' => now(), 'status' => 'responded']);

        return Inertia::render('SupplierPortal/ThankYou', [
            'supplier_name' => $record->supplier_name,
        ]);
    }

    /** Comparateur d'offres (vue interne) */
    public function compare(Request $request, Document $document): Response
    {
        $company = $request->user()->currentCompany;
        if ($document->company_id !== $company->id) {
            abort(403);
        }

        $offers = SupplierPortalToken::where('document_id', $document->id)
            ->orderByDesc('status')
            ->get();

        return Inertia::render('SupplierPortal/Compare', [
            'document' => $document,
            'offers'   => $offers,
        ]);
    }

    /** Sélectionner le meilleur fournisseur */
    public function select(Request $request, SupplierPortalToken $offer)
    {
        $company = $request->user()->currentCompany;
        if ($offer->company_id !== $company->id) {
            abort(403);
        }

        SupplierPortalToken::where('document_id', $offer->document_id)
            ->where('id', '!=', $offer->id)
            ->update(['status' => 'rejected']);

        $offer->update(['status' => 'selected']);

        return back()->with('success', "{$offer->supplier_name} sélectionné comme fournisseur retenu.");
    }
}
