<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\SecurityPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SecurityPolicyController extends Controller
{
    private function getOrCreatePolicy(): SecurityPolicy
    {
        $companyId = Auth::user()->current_company_id;
        return SecurityPolicy::firstOrCreate(
            ['company_id' => $companyId],
            ['company_id' => $companyId]
        );
    }

    public function show(Request $request): Response
    {
        $policy = $this->getOrCreatePolicy();
        $companyId = Auth::user()->current_company_id;

        $stats = [
            'last_logins'     => AccessLog::where('company_id', $companyId)
                ->where('action', 'login')
                ->where('success', true)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
            'failed_attempts' => AccessLog::where('company_id', $companyId)
                ->where('action', 'login_failed')
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count(),
            'recent_logs'     => AccessLog::where('company_id', $companyId)
                ->with('user:id,name,email')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
        ];

        return Inertia::render('Security/Policy', [
            'policy' => $policy,
            'stats'  => $stats,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'password_min_length'       => 'required|integer|min:6|max:128',
            'password_require_uppercase' => 'boolean',
            'password_require_number'    => 'boolean',
            'password_require_symbol'    => 'boolean',
            'password_expiry_days'       => 'required|integer|min:0|max:365',
            'password_history_count'     => 'required|integer|min:0|max:24',
            'session_lifetime_minutes'   => 'required|integer|min:5|max:10080',
            'single_session'             => 'boolean',
            'max_login_attempts'         => 'required|integer|min:1|max:20',
            'lockout_minutes'            => 'required|integer|min:1|max:1440',
            'require_2fa'                => 'boolean',
            'allowed_ips'               => 'nullable|array',
            'allowed_ips.*'             => 'ip',
            'log_all_access'             => 'boolean',
        ]);

        $policy = $this->getOrCreatePolicy();
        $policy->update($validated);

        return back()->with('success', 'Politique de sécurité mise à jour.');
    }

    public function accessLogs(Request $request)
    {
        $companyId = Auth::user()->current_company_id;

        $logs = AccessLog::where('company_id', $companyId)
            ->with('user:id,name,email')
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($logs);
    }

    public function sessions(Request $request)
    {
        $userId = Auth::id();
        $currentSessionId = $request->session()->getId();

        $sessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $payload = @unserialize(base64_decode($session->payload));
                return [
                    'id'            => $session->id,
                    'ip_address'    => $session->ip_address,
                    'user_agent'    => $session->user_agent,
                    'last_activity' => date('Y-m-d H:i:s', $session->last_activity),
                    'is_current'    => $session->id === $currentSessionId,
                ];
            });

        return response()->json($sessions);
    }

    public function killSession(Request $request, string $sessionId)
    {
        $userId = Auth::id();
        $currentSessionId = $request->session()->getId();

        if ($sessionId === $currentSessionId) {
            return back()->with('error', 'Impossible de supprimer la session courante.');
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->delete();

        return back()->with('success', 'Session terminée.');
    }

    public function killAllSessions(Request $request)
    {
        $userId = Auth::id();
        $currentSessionId = $request->session()->getId();

        DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return back()->with('success', 'Toutes les autres sessions ont été terminées.');
    }
}
