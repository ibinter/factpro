<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WhatsAppSettingsController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;

        $token = $company->wa_access_token;
        $maskedToken = $token ? substr($token, 0, 6) . str_repeat('*', max(0, strlen($token) - 10)) . substr($token, -4) : null;

        return Inertia::render('Settings/WhatsApp', [
            'settings' => [
                'wa_enabled'              => $company->wa_enabled,
                'wa_phone_number_id'      => $company->wa_phone_number_id,
                'wa_business_account_id'  => $company->wa_business_account_id,
                'wa_verify_token'         => $company->wa_verify_token,
                'wa_access_token_masked'  => $maskedToken,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'wa_enabled'             => 'boolean',
            'wa_phone_number_id'     => 'nullable|string|max:255',
            'wa_access_token'        => 'nullable|string',
            'wa_business_account_id' => 'nullable|string|max:255',
        ]);

        $company = $request->user()->currentCompany;

        $data = $request->only([
            'wa_enabled',
            'wa_phone_number_id',
            'wa_business_account_id',
        ]);

        // Ne mettre à jour l'access token que s'il est fourni et non masqué
        if ($request->filled('wa_access_token') && ! str_contains($request->wa_access_token, '***')) {
            $data['wa_access_token'] = $request->wa_access_token;
        }

        // Générer un verify token si absent
        if (empty($company->wa_verify_token)) {
            $data['wa_verify_token'] = Str::random(32);
        }

        $company->update($data);

        return back()->with('success', 'Paramètres WhatsApp mis à jour avec succès.');
    }
}
