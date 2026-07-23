<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller {
    public function index(): Response {
        $announcements = Announcement::latest()->get()->map(fn($a) => [
            'id' => $a->id, 'title' => $a->title, 'message' => $a->message,
            'type' => $a->type, 'active' => $a->active,
            'starts_at' => $a->starts_at?->format('d/m/Y H:i'),
            'ends_at' => $a->ends_at?->format('d/m/Y H:i'),
            'created_at' => $a->created_at->format('d/m/Y'),
        ]);
        return Inertia::render('Admin/Announcements', ['announcements' => $announcements]);
    }

    public function store(Request $request): RedirectResponse {
        $request->validate([
            'title'     => 'required|string|max:100',
            'message'   => 'required|string|max:500',
            'type'      => 'required|in:info,success,warning,danger',
            'link_text' => 'nullable|string|max:50',
            'link_url'  => 'nullable|url',
            'starts_at' => 'nullable|date',
            'ends_at'   => 'nullable|date|after_or_equal:starts_at',
        ]);
        Announcement::create($request->only('title', 'message', 'type', 'link_text', 'link_url', 'starts_at', 'ends_at') + ['active' => true]);
        return back()->with('success', 'Annonce créée et diffusée.');
    }

    public function toggle(Announcement $announcement): RedirectResponse {
        $announcement->update(['active' => !$announcement->active]);
        return back()->with('success', $announcement->fresh()->active ? 'Annonce activée.' : 'Annonce désactivée.');
    }

    public function destroy(Announcement $announcement): RedirectResponse {
        $announcement->delete();
        return back()->with('success', 'Annonce supprimée.');
    }
}
