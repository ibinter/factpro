<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendVersionNotifications;
use App\Models\AppVersion;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppVersionController extends Controller
{
    public function index(): Response
    {
        $versions = AppVersion::with('publisher:id,name')
            ->orderByDesc('created_at')
            ->paginate(20);

        $plans = Plan::orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Admin/Versions', [
            'versions' => $versions,
            'plans'    => $plans,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'version'      => 'required|string|max:20',
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'type'         => 'required|in:major,minor,patch',
            'target_plans' => 'nullable|array',
            'target_plans.*' => 'integer|exists:plans,id',
        ]);

        AppVersion::create([
            ...$data,
            'target_plans' => ! empty($data['target_plans']) ? $data['target_plans'] : null,
        ]);

        return back()->with('success', 'Version créée avec succès.');
    }

    public function publish(Request $request, AppVersion $version): RedirectResponse
    {
        if ($version->isPublished()) {
            return back()->with('error', 'Cette version est déjà publiée.');
        }

        $version->update([
            'published_at' => now(),
            'published_by' => $request->user()->id,
        ]);

        // Dispatch du job de notification en queue
        SendVersionNotifications::dispatch($version);

        return back()->with('success', "Version {$version->version} publiée. Les notifications sont en cours d'envoi.");
    }

    public function destroy(AppVersion $version): RedirectResponse
    {
        if ($version->isPublished()) {
            return back()->with('error', 'Impossible de supprimer une version déjà publiée.');
        }

        $version->delete();

        return back()->with('success', 'Version supprimée.');
    }
}
