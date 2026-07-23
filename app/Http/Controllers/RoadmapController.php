<?php
namespace App\Http\Controllers;

use App\Models\RoadmapFeature;
use App\Models\RoadmapVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoadmapController extends Controller {
    public function index(Request $request): Response {
        $userId = $request->user()?->id;

        $features = RoadmapFeature::orderBy('sort_order')
            ->orderByDesc('votes_count')
            ->get()
            ->map(fn($f) => [
                'id'           => $f->id,
                'title'        => $f->title,
                'description'  => $f->description,
                'category'     => $f->category,
                'status'       => $f->status,
                'votes_count'  => $f->votes_count,
                'has_voted'    => $userId ? $f->hasVotedBy($userId) : false,
                'delivered_at' => $f->delivered_at?->format('M Y'),
            ]);

        $stats = [
            'planned'     => $features->where('status', 'planned')->count(),
            'in_progress' => $features->where('status', 'in_progress')->count(),
            'delivered'   => $features->where('status', 'delivered')->count(),
            'total_votes' => $features->sum('votes_count'),
        ];

        return Inertia::render('Public/Roadmap', [
            'features'    => $features,
            'stats'       => $stats,
            'canLogin'    => \Route::has('login'),
            'canRegister' => \Route::has('register'),
            'auth'        => ['user' => $request->user()],
        ]);
    }

    public function vote(Request $request, RoadmapFeature $feature): JsonResponse {
        $userId = $request->user()->id;
        $existing = RoadmapVote::where('feature_id', $feature->id)->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            $feature->decrement('votes_count');
            return response()->json(['voted' => false, 'votes_count' => $feature->fresh()->votes_count]);
        }

        RoadmapVote::create(['feature_id' => $feature->id, 'user_id' => $userId]);
        $feature->increment('votes_count');
        return response()->json(['voted' => true, 'votes_count' => $feature->fresh()->votes_count]);
    }
}
