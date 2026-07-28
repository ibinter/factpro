<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentMethodSettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        return Inertia::render('Settings/PaymentMethods', [
            'methods' => $company->payment_methods ?? [],
            'company' => $company->only(['name', 'currency']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'methods'   => ['required', 'array'],
            'methods.*' => ['array'],
        ]);

        $company = $request->user()->currentCompany;

        $company->update([
            'payment_methods' => $request->input('methods'),
        ]);

        return back()->with('success', 'Méthodes de paiement mises à jour.');
    }
}
