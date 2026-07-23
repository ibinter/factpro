<?php

namespace App\Http\Controllers;

use App\Models\NpsResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NpsController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'score'   => 'required|integer|min:0|max:10',
            'comment' => 'nullable|string|max:500',
        ]);

        // Un seul NPS par user par mois
        $existing = NpsResponse::where('user_id', $request->user()->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->exists();

        if ($existing) {
            return response()->json(['ok' => true, 'already_submitted' => true]);
        }

        NpsResponse::create([
            'user_id' => $request->user()->id,
            'score'   => $request->score,
            'comment' => $request->comment,
            'context' => $request->input('context', 'app'),
        ]);

        return response()->json(['ok' => true]);
    }

    public function adminIndex(Request $request): \Inertia\Response
    {
        $responses = NpsResponse::with('user:id,name,email')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn ($r) => [
                'id'         => $r->id,
                'score'      => $r->score,
                'comment'    => $r->comment,
                'user'       => $r->user?->name,
                'email'      => $r->user?->email,
                'created_at' => $r->created_at->toDateString(),
            ]);

        $total      = NpsResponse::count();
        $promoters  = NpsResponse::where('score', '>=', 9)->count();
        $detractors = NpsResponse::where('score', '<=', 6)->count();
        $nps        = $total > 0
            ? round((($promoters - $detractors) / $total) * 100)
            : 0;

        return \Inertia\Inertia::render('Admin/Nps', [
            'responses' => $responses,
            'stats'     => [
                'nps'        => $nps,
                'total'      => $total,
                'promoters'  => $promoters,
                'detractors' => $detractors,
            ],
        ]);
    }
}
