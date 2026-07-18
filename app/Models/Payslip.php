<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    protected $guarded = [];

    protected $casts = [
        'employee_contributions' => 'array',
        'employer_contributions' => 'array',
        'gross_salary'           => 'decimal:2',
        'net_salary'             => 'decimal:2',
        'total_employer_cost'    => 'decimal:2',
        'payment_date'           => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
