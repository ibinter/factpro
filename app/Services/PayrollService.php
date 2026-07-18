<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Payslip;

class PayrollService
{
    const REGIMES = [
        'cnss_ci' => [
            'name'          => "CNSS Côte d'Ivoire",
            'employee_rate' => 0.063,
            'employer_rate' => 0.195,
            'irpp_brackets' => [
                [0, 75000, 0],
                [75001, 240000, 0.02],
                [240001, 800000, 0.04],
                [800001, 2400000, 0.065],
                [2400001, PHP_INT_MAX, 0.10],
            ],
        ],
        'cnss_sn' => [
            'name'          => 'CNSS Sénégal',
            'employee_rate' => 0.056,
            'employer_rate' => 0.21,
        ],
        'cnss_cm' => [
            'name'          => 'CNSS Cameroun',
            'employee_rate' => 0.042,
            'employer_rate' => 0.177,
        ],
        'urssaf_fr' => [
            'name'          => 'URSSAF France',
            'employee_rate' => 0.22,
            'employer_rate' => 0.42,
        ],
        'custom' => [
            'name'          => 'Régime personnalisé',
            'employee_rate' => 0,
            'employer_rate' => 0,
        ],
    ];

    /**
     * Calcule un bulletin de paie complet.
     *
     * @return array{gross_salary: float, employee_contributions: array, employer_contributions: array, net_salary: float, total_employer_cost: float}
     */
    public function calculate(float $grossSalary, string $regime = 'cnss_ci', array $customRates = []): array
    {
        $rates = self::REGIMES[$regime] ?? self::REGIMES['custom'];

        if ($regime === 'custom') {
            $rates['employee_rate'] = $customRates['employee_rate'] ?? 0;
            $rates['employer_rate'] = $customRates['employer_rate'] ?? 0;
        }

        $employeeContrib = $grossSalary * $rates['employee_rate'];
        $employerContrib = $grossSalary * $rates['employer_rate'];
        $irpp            = $this->calculateIrpp($grossSalary, $regime, $rates);
        $netSalary       = $grossSalary - $employeeContrib - $irpp;
        $totalCost       = $grossSalary + $employerContrib;

        return [
            'gross_salary'           => $grossSalary,
            'employee_contributions' => [
                'cnss'  => round($employeeContrib, 0),
                'irpp'  => round($irpp, 0),
                'total' => round($employeeContrib + $irpp, 0),
            ],
            'employer_contributions' => [
                'cnss'  => round($employerContrib, 0),
                'total' => round($employerContrib, 0),
            ],
            'net_salary'          => round($netSalary, 0),
            'total_employer_cost' => round($totalCost, 0),
        ];
    }

    private function calculateIrpp(float $gross, string $regime, array $rates): float
    {
        if (! isset($rates['irpp_brackets'])) {
            return 0;
        }

        $irpp = 0;
        foreach ($rates['irpp_brackets'] as [$min, $max, $rate]) {
            if ($gross <= $min) {
                break;
            }
            $taxable = min($gross, $max) - $min;
            $irpp   += $taxable * $rate;
        }

        return $irpp;
    }

    /**
     * Génère les bulletins de paie pour tous les employés actifs du mois.
     */
    public function generateMonthlyPayroll(int $companyId, int $month, int $year): array
    {
        $contracts = Contract::where('company_id', $companyId)
            ->where('is_active', true)
            ->with('employee')
            ->get();

        $created = [];
        $skipped = [];

        foreach ($contracts as $contract) {
            $employee = $contract->employee;
            if (! $employee || $employee->status !== 'active') {
                continue;
            }

            // Skip si bulletin déjà existant pour cette période
            $exists = Payslip::where('employee_id', $employee->id)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->exists();

            if ($exists) {
                $skipped[] = $employee->id;
                continue;
            }

            $regime = $employee->social_security_regime ?? 'cnss_ci';
            $result = $this->calculate((float) $contract->gross_salary, $regime);

            $payslip = Payslip::create([
                'employee_id'            => $employee->id,
                'contract_id'            => $contract->id,
                'company_id'             => $companyId,
                'period_month'           => $month,
                'period_year'            => $year,
                'gross_salary'           => $result['gross_salary'],
                'employee_contributions' => $result['employee_contributions'],
                'employer_contributions' => $result['employer_contributions'],
                'net_salary'             => $result['net_salary'],
                'total_employer_cost'    => $result['total_employer_cost'],
                'currency'               => $contract->currency,
                'status'                 => 'draft',
            ]);

            $created[] = $payslip;
        }

        return [
            'created' => count($created),
            'skipped' => count($skipped),
            'payslips' => $created,
        ];
    }

    /** Retourne le label du régime. */
    public function getRegimeName(string $regime): string
    {
        return self::REGIMES[$regime]['name'] ?? 'Inconnu';
    }
}
