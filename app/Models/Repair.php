<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Repair extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'ticket_number',
        'device_type',
        'brand',
        'model_name',
        'serial_number',
        'issue_description',
        'diagnosis',
        'status',
        'priority',
        'technician_name',
        'estimated_cost',
        'final_cost',
        'deposit_amount',
        'received_at',
        'promised_at',
        'delivered_at',
        'internal_notes',
        'customer_notes',
    ];

    protected $casts = [
        'received_at'    => 'datetime',
        'promised_at'    => 'datetime',
        'delivered_at'   => 'datetime',
        'estimated_cost' => 'decimal:2',
        'final_cost'     => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'status'         => 'string',
        'priority'       => 'string',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(RepairPart::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'received'      => 'Reçu',
            'diagnosing'    => 'Diagnostic',
            'waiting_parts' => 'Attente pièces',
            'repairing'     => 'En réparation',
            'ready'         => 'Prêt',
            'delivered'     => 'Livré',
            'cancelled'     => 'Annulé',
        ][$this->status] ?? $this->status;
    }

    public function getPriorityLabelAttribute(): string
    {
        return [
            'low'    => 'Basse',
            'normal' => 'Normale',
            'high'   => 'Haute',
            'urgent' => 'Urgente',
        ][$this->priority] ?? $this->priority;
    }

    public static function generateTicketNumber(Company $company): string
    {
        $count = self::where('company_id', $company->id)->count() + 1;
        return 'SAV-' . date('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
