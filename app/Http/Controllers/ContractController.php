<?php

namespace App\Http\Controllers;

use App\Models\CommercialContract;
use App\Models\ContractVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ContractController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $query = CommercialContract::where('company_id', $company->id)
            ->with(['customer'])
            ->withCount('versions');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }

        $contracts = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Contracts/Index', [
            'contracts' => $contracts,
            'filters'   => $request->only(['status', 'type', 'search']),
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $company = $request->user()->currentCompany;

        $contract = CommercialContract::where('company_id', $company->id)
            ->with(['customer', 'versions.creator'])
            ->findOrFail($id);

        return Inertia::render('Contracts/Show', [
            'contract' => $contract,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'type'              => 'required|in:service,prestation,maintenance,nda,other',
            'start_date'        => 'required|date',
            'end_date'          => 'nullable|date|after:start_date',
            'auto_renew'        => 'boolean',
            'alert_days_before' => 'integer|min:1|max:365',
            'amount'            => 'nullable|numeric|min:0',
            'currency'          => 'nullable|string|max:5',
            'status'            => 'required|in:draft,active,expired,terminated',
            'notes'             => 'nullable|string',
            'customer_id'       => 'nullable|exists:customers,id',
        ]);

        $company = $request->user()->currentCompany;

        CommercialContract::create(array_merge($validated, [
            'company_id' => $company->id,
        ]));

        return redirect()->route('contracts.index')
            ->with('success', 'Contrat créé avec succès.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        $contract = CommercialContract::where('company_id', $company->id)->findOrFail($id);

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'type'              => 'required|in:service,prestation,maintenance,nda,other',
            'start_date'        => 'required|date',
            'end_date'          => 'nullable|date|after:start_date',
            'auto_renew'        => 'boolean',
            'alert_days_before' => 'integer|min:1|max:365',
            'amount'            => 'nullable|numeric|min:0',
            'currency'          => 'nullable|string|max:5',
            'status'            => 'required|in:draft,active,expired,terminated',
            'notes'             => 'nullable|string',
            'customer_id'       => 'nullable|exists:customers,id',
        ]);

        $contract->update($validated);

        return redirect()->route('contracts.index')
            ->with('success', 'Contrat mis à jour.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        $contract = CommercialContract::where('company_id', $company->id)->findOrFail($id);
        $contract->delete();

        return redirect()->route('contracts.index')
            ->with('success', 'Contrat supprimé.');
    }

    public function uploadVersion(Request $request, CommercialContract $contract): RedirectResponse
    {
        $request->validate([
            'file'         => 'required|file|max:51200', // 50 MB
            'change_notes' => 'nullable|string|max:1000',
        ]);

        $file      = $request->file('file');
        $companyId = $contract->company_id;
        $path      = $file->store("contracts/{$companyId}", 'local');

        $nextVersion = ($contract->current_version ?? 0) + 1;

        ContractVersion::create([
            'contract_id'    => $contract->id,
            'version_number' => $nextVersion,
            'file_path'      => $path,
            'file_name'      => $file->getClientOriginalName(),
            'mime_type'      => $file->getMimeType(),
            'file_size'      => $file->getSize(),
            'change_notes'   => $request->change_notes,
            'created_by'     => $request->user()->id,
        ]);

        $contract->update(['current_version' => $nextVersion]);

        return back()->with('success', "Version {$nextVersion} uploadée.");
    }
}
