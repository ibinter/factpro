<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetDepreciation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $assets = Asset::where('company_id', $company->id)
            ->with('depreciations')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => [
                'id'             => $a->id,
                'name'           => $a->name,
                'category'       => $a->category,
                'reference'      => $a->reference,
                'purchase_price' => $a->purchase_price,
                'purchase_date'  => $a->purchase_date?->format('Y-m-d'),
                'duration_years' => $a->duration_years,
                'method'         => $a->depreciation_method,
                'status'         => $a->status,
                'net_book_value' => $a->current_net_book_value,
                'currency'       => $a->currency,
            ]);

        $totals = [
            'total_purchase' => $assets->sum('purchase_price'),
            'total_nbv'      => $assets->sum('net_book_value'),
            'count'          => $assets->count(),
            'active'         => $assets->where('status', 'active')->count(),
        ];

        return Inertia::render('Assets/Index', compact('assets', 'totals'));
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;
        $data    = $request->validate([
            'name'                => 'required|string|max:255',
            'category'            => 'required|string',
            'reference'           => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'purchase_price'      => 'required|numeric|min:0',
            'residual_value'      => 'nullable|numeric|min:0',
            'purchase_date'       => 'required|date',
            'start_date'          => 'required|date',
            'duration_years'      => 'required|integer|min:1|max:50',
            'depreciation_method' => 'required|in:linear,declining',
            'supplier'            => 'nullable|string|max:255',
            'location'            => 'nullable|string|max:255',
            'serial_number'       => 'nullable|string|max:100',
            'currency'            => 'nullable|string|max:10',
        ]);

        $asset = Asset::create(['company_id' => $company->id] + $data);
        $this->saveSchedule($asset);

        return redirect()->route('assets.index')->with('success', 'Immobilisation ajoutée.');
    }

    public function show(Request $request, Asset $asset): Response
    {
        if ($asset->company_id !== $request->user()->currentCompany->id) {
            abort(403);
        }
        $asset->load('depreciations');

        return Inertia::render('Assets/Show', [
            'asset'    => $asset,
            'schedule' => $asset->computeDepreciationSchedule(),
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        if ($asset->company_id !== $request->user()->currentCompany->id) {
            abort(403);
        }
        $data = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'status'        => 'sometimes|in:active,disposed,written_off',
            'disposal_date' => 'nullable|date',
            'disposal_price'=> 'nullable|numeric|min:0',
            'location'      => 'nullable|string',
        ]);
        $asset->update($data);
        if (in_array($data['status'] ?? '', ['active'])) {
            $this->saveSchedule($asset);
        }

        return back()->with('success', 'Immobilisation mise à jour.');
    }

    public function destroy(Request $request, Asset $asset)
    {
        if ($asset->company_id !== $request->user()->currentCompany->id) {
            abort(403);
        }
        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Immobilisation supprimée.');
    }

    private function saveSchedule(Asset $asset): void
    {
        $schedule = $asset->computeDepreciationSchedule();
        AssetDepreciation::where('asset_id', $asset->id)->delete();
        foreach ($schedule as $row) {
            AssetDepreciation::create(['asset_id' => $asset->id] + $row);
        }
    }
}
