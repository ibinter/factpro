<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\Payslip;
use App\Services\LicenseService;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class HrController extends Controller
{
    private const ALLOWED_PLANS = ['enterprise'];

    public function __construct(
        private LicenseService $licenses,
        private PayrollService $payroll,
    ) {}

    private function hasAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && in_array($license->plan?->code, self::ALLOWED_PLANS, true);
    }

    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        if (! $this->hasAccess($request)) {
            return Inertia::render('Hr/Index', [
                'hasAccess' => false,
                'employees' => [],
                'stats'     => [],
                'payslips'  => [],
            ]);
        }

        $employees = Employee::where('company_id', $company->id)
            ->with('activeContract')
            ->get()
            ->map(fn ($e) => [
                'id'         => $e->id,
                'full_name'  => $e->full_name,
                'first_name' => $e->first_name,
                'last_name'  => $e->last_name,
                'position'   => $e->position,
                'department' => $e->department,
                'status'     => $e->status,
                'hire_date'  => $e->hire_date?->toDateString(),
                'regime'     => $e->social_security_regime,
                'gross_salary' => $e->activeContract?->gross_salary,
                'contract_type' => $e->activeContract?->type,
            ]);

        $activeCount       = Employee::where('company_id', $company->id)->where('status', 'active')->count();
        $activeContracts   = Contract::where('company_id', $company->id)->where('is_active', true);
        $masseSalariale    = (float) (clone $activeContracts)->sum('gross_salary');
        $chargesPatronales = $masseSalariale * 0.195; // CNSS CI par défaut

        $recentPayslips = Payslip::where('company_id', $company->id)
            ->with('employee:id,first_name,last_name')
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Hr/Index', [
            'hasAccess' => true,
            'employees' => $employees,
            'payslips'  => $recentPayslips,
            'stats'     => [
                'active_employees'  => $activeCount,
                'masse_salariale'   => $masseSalariale,
                'charges_patronales' => round($chargesPatronales, 0),
                'total_cost'        => round($masseSalariale + $chargesPatronales, 0),
            ],
        ]);
    }

    public function storeEmployee(Request $request): RedirectResponse
    {
        abort_unless($this->hasAccess($request), 403);

        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'first_name'             => 'required|string|max:100',
            'last_name'              => 'required|string|max:100',
            'email'                  => 'nullable|email|max:255',
            'phone'                  => 'nullable|string|max:50',
            'position'               => 'required|string|max:100',
            'department'             => 'nullable|string|max:100',
            'hire_date'              => 'required|date',
            'social_security_regime' => 'nullable|string|max:20',
            'cnss_number'            => 'nullable|string|max:50',
            'bank_iban'              => 'nullable|string|max:50',
            'bank_name'              => 'nullable|string|max:100',
            // Contract fields
            'contract_type'    => 'required|in:cdi,cdd,stage,freelance',
            'gross_salary'     => 'required|numeric|min:0',
            'currency'         => 'nullable|string|max:3',
            'contract_start'   => 'required|date',
        ]);

        $employee = Employee::create([
            'company_id'             => $company->id,
            'first_name'             => $validated['first_name'],
            'last_name'              => $validated['last_name'],
            'email'                  => $validated['email'] ?? null,
            'phone'                  => $validated['phone'] ?? null,
            'position'               => $validated['position'],
            'department'             => $validated['department'] ?? null,
            'hire_date'              => $validated['hire_date'],
            'social_security_regime' => $validated['social_security_regime'] ?? 'cnss_ci',
            'cnss_number'            => $validated['cnss_number'] ?? null,
            'bank_iban'              => $validated['bank_iban'] ?? null,
            'bank_name'              => $validated['bank_name'] ?? null,
        ]);

        Contract::create([
            'employee_id'  => $employee->id,
            'company_id'   => $company->id,
            'type'         => $validated['contract_type'],
            'start_date'   => $validated['contract_start'],
            'gross_salary' => $validated['gross_salary'],
            'currency'     => $validated['currency'] ?? 'XOF',
            'is_active'    => true,
        ]);

        return redirect()->route('hr.index')->with('success', 'Employé créé avec succès.');
    }

    public function updateEmployee(Employee $employee, Request $request): RedirectResponse
    {
        abort_unless($this->hasAccess($request), 403);
        abort_unless($employee->company_id === $request->user()->current_company_id, 403);

        $validated = $request->validate([
            'first_name'             => 'sometimes|string|max:100',
            'last_name'              => 'sometimes|string|max:100',
            'email'                  => 'nullable|email|max:255',
            'phone'                  => 'nullable|string|max:50',
            'position'               => 'sometimes|string|max:100',
            'department'             => 'nullable|string|max:100',
            'status'                 => 'sometimes|in:active,suspended,terminated',
            'social_security_regime' => 'nullable|string|max:20',
            'cnss_number'            => 'nullable|string|max:50',
        ]);

        $employee->update($validated);

        return redirect()->route('hr.index')->with('success', 'Employé mis à jour.');
    }

    public function generatePayroll(Request $request): RedirectResponse
    {
        abort_unless($this->hasAccess($request), 403);

        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2020|max:2100',
        ]);

        $result = $this->payroll->generateMonthlyPayroll(
            $company->id,
            (int) $validated['month'],
            (int) $validated['year']
        );

        return redirect()->route('hr.index')->with('success', "{$result['created']} bulletin(s) généré(s), {$result['skipped']} ignoré(s).");
    }

    public function payslips(Request $request): Response
    {
        abort_unless($this->hasAccess($request), 403);

        $company = $request->user()->currentCompany;

        $query = Payslip::where('company_id', $company->id)
            ->with('employee:id,first_name,last_name');

        if ($request->filled('month')) {
            $query->where('period_month', $request->integer('month'));
        }
        if ($request->filled('year')) {
            $query->where('period_year', $request->integer('year'));
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->integer('employee_id'));
        }

        $payslips = $query->latest()->paginate(25);

        return Inertia::render('Hr/Payslip', [
            'payslips' => $payslips,
            'filters'  => $request->only(['month', 'year', 'employee_id']),
        ]);
    }

    public function showPayslip(Payslip $payslip): Response
    {
        abort_unless(
            $payslip->company_id === request()->user()->current_company_id,
            403
        );

        $payslip->load(['employee', 'contract', 'company']);

        return Inertia::render('Hr/Payslip', [
            'payslip' => $payslip,
        ]);
    }

    public function pdfPayslip(Payslip $payslip): HttpResponse
    {
        abort_unless(
            $payslip->company_id === request()->user()->current_company_id,
            403
        );

        $payslip->load(['employee', 'contract', 'company']);

        $pdf = Pdf::loadView('pdf.payslip', ['payslip' => $payslip])
            ->setPaper('a4', 'portrait');

        $filename = "bulletin-paie-{$payslip->employee->last_name}-{$payslip->period_year}-{$payslip->period_month}.pdf";

        return $pdf->download($filename);
    }

    public function validatePayslip(Payslip $payslip): RedirectResponse
    {
        abort_unless(
            $payslip->company_id === request()->user()->current_company_id,
            403
        );

        $payslip->update(['status' => 'validated']);

        return redirect()->route('hr.index')->with('success', 'Bulletin validé.');
    }

    public function massAction(Request $request): RedirectResponse
    {
        abort_unless($this->hasAccess($request), 403);

        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'action'      => 'required|in:pay',
            'payslip_ids' => 'required|array',
            'payslip_ids.*' => 'integer',
        ]);

        if ($validated['action'] === 'pay') {
            Payslip::whereIn('id', $validated['payslip_ids'])
                ->where('company_id', $company->id)
                ->where('status', 'validated')
                ->update([
                    'status'       => 'paid',
                    'payment_date' => now()->toDateString(),
                ]);
        }

        return redirect()->route('hr.index')->with('success', 'Action effectuée.');
    }
}
