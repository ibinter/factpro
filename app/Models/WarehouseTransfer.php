<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseTransfer extends Model
{
    protected $fillable = [
        'company_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'reference',
        'status',
        'notes',
        'transferred_at',
        'received_at',
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(WarehouseTransferLine::class, 'transfer_id');
    }

    public function execute(): void
    {
        foreach ($this->lines as $line) {
            $qtySent = $line->quantity_sent;
            $qtyReceived = $line->quantity_received ?? $qtySent;

            $this->fromWarehouse->adjustStock($line->product_id, -$qtySent);
            $this->toWarehouse->adjustStock($line->product_id, $qtyReceived);
        }

        $this->update([
            'status' => 'received',
            'received_at' => now(),
        ]);
    }
}
