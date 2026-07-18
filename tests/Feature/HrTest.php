<?php

use App\Models\Contract;
use App\Models\Employee;
use App\Models\License;
use App\Models\Payslip;
use App\Models\Plan;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\PayrollService;

// -------------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------------

function createHrEnterpriseLicense(User $user): License
{
    seedPlans();
    $plan = Plan::where('code', 'enterprise')->firstOrFail();

    return License::create([
        'user_id'           => $user->id,
        'plan_id'           => $plan->id,
        'license_key'       => app(LicenseService::class)->generateKey(),
        'type'              => 'paid',
        'status'            => 'active',
        'starts_at'         => now(),
        'ends_at'           => now()->addYear(),
        'limits'            => $plan->limits,
        'activation_source' => 'manual',
    ]);
}

function createEmployeeFor(int $companyId, array $attrs = []): Employee
{
    return Employee::create(array_merge([
        'company_id'             => $companyId,
        'first_name'             => 'Jean',
        'last_name'              => 'Dupont',
        'position'               => 'Développeur',
        'hire_date'              => '2023-01-15',
        'social_security_regime' => 'cnss_ci',
        'status'                 => 'active',
    ], $attrs));
}

function createContractFor(int $employeeId, int $companyId, array $attrs = []): Contract
{
    return Contract::create(array_merge([
        'employee_id'  => $employeeId,
        'company_id'   => $companyId,
        'type'         => 'cdi',
        'start_date'   => '2023-01-15',
        'gross_salary' => 500000,
        'currency'     => 'XOF',
        'is_active'    => true,
    ], $attrs));
}

// -------------------------------------------------------------------------
// Setup
// -------------------------------------------------------------------------

beforeEach(function () {
    $this->user    = createUserWithCompany();
    $this->company = $this->user->currentCompany;
    createHrEnterpriseLicense($this->user);
});

// -------------------------------------------------------------------------
// Tests
// -------------------------------------------------------------------------

it('creates an employee with contract', function () {
    $response = $this->actingAs($this->user)->post(route('hr.employees.store'), [
        'first_name'    => 'Aminata',
        'last_name'     => 'Koné',
        'position'      => 'Comptable',
        'hire_date'     => '2024-01-01',
        'contract_type' => 'cdi',
        'gross_salary'  => 300000,
        'contract_start' => '2024-01-01',
    ]);

    $response->assertRedirect();

    expect(Employee::where('first_name', 'Aminata')->exists())->toBeTrue();
    expect(Contract::where('gross_salary', 300000)->exists())->toBeTrue();
});

it('calculates cnss_ci payroll correctly', function () {
    $service = app(PayrollService::class);
    $result  = $service->calculate(500000, 'cnss_ci');

    expect($result['gross_salary'])->toBe(500000.0);
    // Salarial : 500000 * 0.063 = 31500
    expect($result['employee_contributions']['cnss'])->toBe(31500.0);
    // Patronal : 500000 * 0.195 = 97500
    expect($result['employer_contributions']['cnss'])->toBe(97500.0);
    // Net = brut - cnss_sal - irpp
    expect($result['net_salary'])->toBeLessThan(500000);
    // Total coût = brut + charges patronales
    expect($result['total_employer_cost'])->toBe(597500.0);
});

it('calculates urssaf_fr payroll correctly', function () {
    $service = app(PayrollService::class);
    $result  = $service->calculate(3000, 'urssaf_fr');

    expect($result['employee_contributions']['cnss'])->toBe(round(3000 * 0.22, 0));
    expect($result['employer_contributions']['cnss'])->toBe(round(3000 * 0.42, 0));
    expect($result['total_employer_cost'])->toBe(round(3000 + 3000 * 0.42, 0));
    // Pas d'IRPP pour URSSAF
    expect($result['employee_contributions']['irpp'])->toBe(0.0);
});

it('generates monthly payslips for all active employees', function () {
    $emp1 = createEmployeeFor($this->company->id, ['first_name' => 'Alice', 'last_name' => 'Martin']);
    createContractFor($emp1->id, $this->company->id, ['gross_salary' => 400000]);

    $emp2 = createEmployeeFor($this->company->id, ['first_name' => 'Bob', 'last_name' => 'Diallo']);
    createContractFor($emp2->id, $this->company->id, ['gross_salary' => 600000]);

    $result = app(PayrollService::class)->generateMonthlyPayroll($this->company->id, 7, 2026);

    expect($result['created'])->toBe(2);
    expect(Payslip::where('company_id', $this->company->id)->count())->toBe(2);
});

it('validates a payslip', function () {
    $emp     = createEmployeeFor($this->company->id);
    $contract = createContractFor($emp->id, $this->company->id);

    $payslip = Payslip::create([
        'employee_id'            => $emp->id,
        'contract_id'            => $contract->id,
        'company_id'             => $this->company->id,
        'period_month'           => 7,
        'period_year'            => 2026,
        'gross_salary'           => 500000,
        'employee_contributions' => ['cnss' => 31500, 'irpp' => 0, 'total' => 31500],
        'employer_contributions' => ['cnss' => 97500, 'total' => 97500],
        'net_salary'             => 468500,
        'total_employer_cost'    => 597500,
        'currency'               => 'XOF',
        'status'                 => 'draft',
    ]);

    $this->actingAs($this->user)
        ->post(route('hr.payslips.validate', $payslip))
        ->assertRedirect();

    expect($payslip->fresh()->status)->toBe('validated');
});

it('generates pdf payslip', function () {
    $emp      = createEmployeeFor($this->company->id);
    $contract = createContractFor($emp->id, $this->company->id);

    $payslip = Payslip::create([
        'employee_id'            => $emp->id,
        'contract_id'            => $contract->id,
        'company_id'             => $this->company->id,
        'period_month'           => 7,
        'period_year'            => 2026,
        'gross_salary'           => 500000,
        'employee_contributions' => ['cnss' => 31500, 'irpp' => 0, 'total' => 31500],
        'employer_contributions' => ['cnss' => 97500, 'total' => 97500],
        'net_salary'             => 468500,
        'total_employer_cost'    => 597500,
        'currency'               => 'XOF',
        'status'                 => 'draft',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('hr.payslips.pdf', $payslip));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});

it('isolates employees between companies', function () {
    $otherUser    = createUserWithCompany();
    $otherCompany = $otherUser->currentCompany;
    createHrEnterpriseLicense($otherUser);

    $emp = createEmployeeFor($otherCompany->id, ['first_name' => 'Stranger']);

    // Notre user ne doit pas voir l'employé de l'autre société
    $count = Employee::where('company_id', $this->company->id)->count();
    expect($count)->toBe(0);

    expect(Employee::where('company_id', $otherCompany->id)->first()->first_name)->toBe('Stranger');
});

it('rejects enterprise gate for non-enterprise plan', function () {
    $freeUser = createUserWithCompany();
    // Licence PRO seulement
    seedPlans();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    License::create([
        'user_id'           => $freeUser->id,
        'plan_id'           => $plan->id,
        'license_key'       => app(LicenseService::class)->generateKey(),
        'type'              => 'paid',
        'status'            => 'active',
        'starts_at'         => now(),
        'ends_at'           => now()->addYear(),
        'limits'            => $plan->limits,
        'activation_source' => 'manual',
    ]);

    $response = $this->actingAs($freeUser)->post(route('hr.employees.store'), [
        'first_name'    => 'Test',
        'last_name'     => 'User',
        'position'      => 'Dev',
        'hire_date'     => '2024-01-01',
        'contract_type' => 'cdi',
        'gross_salary'  => 200000,
        'contract_start' => '2024-01-01',
    ]);

    $response->assertStatus(403);
});

it('net salary is gross minus contributions', function () {
    $service = app(PayrollService::class);
    $gross   = 800000;
    $result  = $service->calculate($gross, 'cnss_ci');

    $expectedNet = $gross - $result['employee_contributions']['total'];
    expect($result['net_salary'])->toBe($expectedNet);
});

it('unique constraint prevents duplicate payslips for same period', function () {
    $emp      = createEmployeeFor($this->company->id);
    $contract = createContractFor($emp->id, $this->company->id);

    $data = [
        'employee_id'            => $emp->id,
        'contract_id'            => $contract->id,
        'company_id'             => $this->company->id,
        'period_month'           => 6,
        'period_year'            => 2026,
        'gross_salary'           => 400000,
        'employee_contributions' => ['cnss' => 25200, 'irpp' => 0, 'total' => 25200],
        'employer_contributions' => ['cnss' => 78000, 'total' => 78000],
        'net_salary'             => 374800,
        'total_employer_cost'    => 478000,
        'currency'               => 'XOF',
        'status'                 => 'draft',
    ];

    Payslip::create($data);

    expect(fn () => Payslip::create($data))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
