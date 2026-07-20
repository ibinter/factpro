<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CryptoWallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CryptoWalletAdminController extends Controller
{
    public function index(): Response
    {
        $wallets = CryptoWallet::withTrashed()->orderBy('display_order')->get();

        return Inertia::render('Admin/CryptoWallets', [
            'wallets' => $wallets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'currency'               => 'required|string|max:20',
            'network'                => 'required|string|max:30',
            'wallet_address'         => 'required|string|max:200',
            'label'                  => 'nullable|string|max:100',
            'qr_code_url'            => 'nullable|url|max:255',
            'instructions'           => 'nullable|string',
            'confirmations_required' => 'nullable|integer|min:1|max:100',
            'is_active'              => 'boolean',
            'display_order'          => 'nullable|integer',
        ]);

        CryptoWallet::create($data);

        return redirect()->route('admin.crypto-wallets.index')->with('success', 'Wallet crypto ajouté.');
    }

    public function update(Request $request, CryptoWallet $cryptoWallet): RedirectResponse
    {
        $data = $request->validate([
            'currency'               => 'sometimes|string|max:20',
            'network'                => 'sometimes|string|max:30',
            'wallet_address'         => 'sometimes|string|max:200',
            'label'                  => 'nullable|string|max:100',
            'qr_code_url'            => 'nullable|url|max:255',
            'instructions'           => 'nullable|string',
            'confirmations_required' => 'nullable|integer|min:1|max:100',
            'is_active'              => 'boolean',
            'display_order'          => 'nullable|integer',
        ]);

        $cryptoWallet->update($data);

        return redirect()->route('admin.crypto-wallets.index')->with('success', 'Wallet crypto mis à jour.');
    }

    public function destroy(CryptoWallet $cryptoWallet): RedirectResponse
    {
        $cryptoWallet->delete();

        return redirect()->route('admin.crypto-wallets.index')->with('success', 'Wallet crypto supprimé.');
    }
}
