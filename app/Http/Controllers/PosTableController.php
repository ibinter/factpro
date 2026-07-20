<?php

namespace App\Http\Controllers;

use App\Models\PosTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PosTableController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        return Inertia::render('Pos/Tables', [
            'tables' => PosTable::where('company_id', $company->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'seats' => 'nullable|integer|min:1|max:99',
        ]);

        PosTable::create([
            'company_id' => $request->user()->current_company_id,
            'name' => $data['name'],
            'seats' => $data['seats'] ?? 4,
        ]);

        return redirect()->route('pos.tables.index')->with('success', 'Table créée.');
    }

    public function update(Request $request, PosTable $table): RedirectResponse
    {
        abort_unless($table->company_id === $request->user()->current_company_id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:50',
            'seats' => 'nullable|integer|min:1|max:99',
            'status' => 'nullable|in:free,occupied,reserved',
        ]);

        $table->update($data);

        return redirect()->route('pos.tables.index')->with('success', 'Table mise à jour.');
    }

    public function destroy(Request $request, PosTable $table): RedirectResponse
    {
        abort_unless($table->company_id === $request->user()->current_company_id, 403);

        if ($table->status !== 'free') {
            return back()->with('error', 'Impossible de supprimer une table occupée ou réservée.');
        }

        $table->delete();

        return redirect()->route('pos.tables.index')->with('success', 'Table supprimée.');
    }

    public function assignOrder(Request $request, PosTable $table): JsonResponse
    {
        abort_unless($table->company_id === $request->user()->current_company_id, 403);

        $data = $request->validate([
            'order_data' => 'required|array',
            'session_id' => 'nullable|exists:pos_sessions,id',
        ]);

        $table->update([
            'status' => 'occupied',
            'order_data' => $data['order_data'],
            'current_pos_session_id' => $data['session_id'] ?? $table->current_pos_session_id,
        ]);

        return response()->json(['success' => true, 'table' => $table->fresh()]);
    }

    public function freeTable(Request $request, PosTable $table): JsonResponse
    {
        abort_unless($table->company_id === $request->user()->current_company_id, 403);

        $table->update([
            'status' => 'free',
            'order_data' => null,
            'current_pos_session_id' => null,
        ]);

        return response()->json(['success' => true, 'table' => $table->fresh()]);
    }
}
