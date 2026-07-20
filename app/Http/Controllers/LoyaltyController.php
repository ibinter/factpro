<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyReward;
use App\Services\LoyaltyService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoyaltyController extends Controller
{
    public function __construct(private LoyaltyService $loyalty) {}

    public function dashboard(Request $request): Response
    {
        $company = $request->user()->currentCompany;
        $program = LoyaltyProgram::where('company_id', $company->id)->first();

        $membersCount = LoyaltyPoint::where('company_id', $company->id)
            ->distinct('customer_id')->count('customer_id');

        $pointsThisMonth = (int) LoyaltyPoint::where('company_id', $company->id)
            ->where('type', 'earned')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('points');

        $redemptionsCount = LoyaltyPoint::where('company_id', $company->id)
            ->where('type', 'redeemed')
            ->count();

        $rewards = $program
            ? LoyaltyReward::where('company_id', $company->id)->where('is_active', true)->get()
            : collect();

        $topCustomers = $this->loyalty->topCustomers($company->id);

        // Points par mois sur 6 mois
        $monthlyPoints = LoyaltyPoint::where('company_id', $company->id)
            ->where('type', 'earned')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(points) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('Loyalty/Index', [
            'program' => $program,
            'stats' => [
                'members_count' => $membersCount,
                'points_this_month' => $pointsThisMonth,
                'redemptions_count' => $redemptionsCount,
            ],
            'rewards' => $rewards,
            'topCustomers' => $topCustomers->map(fn ($row) => [
                'customer' => $row->customer,
                'total_points' => (int) $row->total_points,
                'level' => $program ? $this->loyalty->getLevel((int) $row->total_points, $program) : null,
            ])->values(),
            'monthlyPoints' => $monthlyPoints,
        ]);
    }

    public function setupProgram(Request $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'name' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'points_per_1000' => 'required|integer|min:1',
            'currency' => 'nullable|string|size:3',
            'bronze_threshold' => 'nullable|integer|min:0',
            'silver_threshold' => 'required|integer|min:0',
            'gold_threshold' => 'required|integer|min:0',
            'expiry_months' => 'nullable|integer|min:1',
        ]);

        LoyaltyProgram::updateOrCreate(
            ['company_id' => $company->id],
            array_filter($data, fn ($v) => $v !== null) + ['company_id' => $company->id]
        );

        return redirect()->route('loyalty.index')->with('success', 'Programme de fidélité configuré.');
    }

    public function customerPoints(Request $request, Customer $customer): JsonResponse
    {
        $company = $request->user()->currentCompany;
        abort_unless($customer->company_id === $company->id, 403);

        $program = LoyaltyProgram::where('company_id', $company->id)->first();
        $balance = $this->loyalty->getBalance($customer->id, $company->id);
        $level = $program ? $this->loyalty->getLevel($balance, $program) : null;

        $history = LoyaltyPoint::where('customer_id', $customer->id)
            ->where('company_id', $company->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'customer' => $customer,
            'balance' => $balance,
            'level' => $level,
            'history' => $history,
        ]);
    }

    public function redeemReward(Request $request): RedirectResponse|JsonResponse
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'reward_id' => 'required|exists:loyalty_rewards,id',
        ]);

        $reward = LoyaltyReward::where('id', $data['reward_id'])
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->firstOrFail();

        try {
            $code = $this->loyalty->redeemReward($data['customer_id'], $company->id, $reward);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('loyalty.index')->with('success', "Récompense échangée ! Code coupon : {$code}");
    }

    public function storeReward(Request $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'points_cost' => 'required|integer|min:1',
            'reward_type' => 'required|in:discount_percent,discount_fixed,free_product,gift',
            'reward_value' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'stock' => 'nullable|integer|min:0',
        ]);

        LoyaltyReward::create($data + ['company_id' => $company->id]);

        return redirect()->route('loyalty.index')->with('success', 'Récompense créée.');
    }

    public function cardPdf(Request $request, Customer $customer)
    {
        $company = $request->user()->currentCompany;
        abort_unless($customer->company_id === $company->id, 403);

        $program = LoyaltyProgram::where('company_id', $company->id)->first();
        $balance = $this->loyalty->getBalance($customer->id, $company->id);
        $level = $program ? $this->loyalty->getLevel($balance, $program) : ['name' => 'Bronze', 'color' => '#B45309', 'icon' => '🥉'];

        $pdf = Pdf::loadView('pdf.loyalty-card', [
            'customer' => $customer,
            'company' => $company,
            'program' => $program,
            'balance' => $balance,
            'level' => $level,
        ])->setPaper([0, 0, 297.64, 419.53]); // A6 landscape in points

        return $pdf->stream("carte-fidelite-{$customer->id}.pdf");
    }

    public function topCustomers(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany;
        $program = LoyaltyProgram::where('company_id', $company->id)->first();

        $top = $this->loyalty->topCustomers($company->id)->map(fn ($row) => [
            'customer' => $row->customer,
            'total_points' => (int) $row->total_points,
            'level' => $program ? $this->loyalty->getLevel((int) $row->total_points, $program) : null,
        ])->values();

        return response()->json($top);
    }
}
