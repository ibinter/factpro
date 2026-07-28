<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'reference', 'type', 'address', 'city', 'country',
        'area_sqm', 'bedrooms', 'bathrooms', 'floor', 'total_floors',
        'monthly_rent', 'currency', 'purchase_price', 'purchase_date',
        'tax_rate', 'status', 'description', 'amenities',
    ];

    protected $casts = [
        'amenities' => 'array',
        'purchase_date' => 'date',
        'area_sqm' => 'decimal:2',
        'monthly_rent' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease(): HasOne
    {
        return $this->hasOne(Lease::class)->where('status', 'active');
    }

    public function rentPayments(): HasManyThrough
    {
        return $this->hasManyThrough(RentPayment::class, Lease::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return [
            'apartment'  => 'Appartement',
            'house'      => 'Maison',
            'villa'      => 'Villa',
            'commercial' => 'Local commercial',
            'office'     => 'Bureau',
            'warehouse'  => 'Entrepôt',
            'land'       => 'Terrain',
            'parking'    => 'Parking',
        ][$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'available'   => 'Disponible',
            'rented'      => 'Loué',
            'maintenance' => 'En maintenance',
            'for_sale'    => 'À vendre',
        ][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return [
            'available'   => 'green',
            'rented'      => 'blue',
            'maintenance' => 'orange',
            'for_sale'    => 'purple',
        ][$this->status] ?? 'gray';
    }

    public function generateRentInvoice(Lease $lease, Carbon $period): Document
    {
        $periodLabel = mb_strtoupper($period->translatedFormat('F Y'));

        $document = Document::create([
            'company_id'  => $this->company_id,
            'customer_id' => $lease->customer_id,
            'type'        => 'invoice',
            'status'      => 'draft',
            'issue_date'  => today(),
            'due_date'    => today()->addDays(30),
            'currency'    => $this->currency,
            'notes'       => 'Loyer ' . $periodLabel,
            'subtotal'    => $lease->monthly_rent,
            'tax_amount'  => round($lease->monthly_rent * $this->tax_rate / 100, 2),
            'discount_amount' => 0,
            'total'       => round($lease->monthly_rent * (1 + $this->tax_rate / 100), 2),
        ]);

        DocumentLine::create([
            'document_id' => $document->id,
            'description' => 'Loyer ' . $periodLabel,
            'quantity'    => 1,
            'unit_price'  => $lease->monthly_rent,
            'tax_rate'    => $this->tax_rate,
            'discount'    => 0,
            'line_total'  => round($lease->monthly_rent * (1 + $this->tax_rate / 100), 2),
        ]);

        return $document;
    }
}
