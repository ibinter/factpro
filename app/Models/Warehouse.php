<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address',
        'city',
        'manager_name',
        'phone',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(WarehouseTransfer::class, 'from_warehouse_id');
    }

    public function getStock(int $productId): float
    {
        return WarehouseStock::where('warehouse_id', $this->id)
            ->where('product_id', $productId)
            ->value('quantity') ?? 0;
    }

    public function adjustStock(int $productId, float $delta): WarehouseStock
    {
        $stock = WarehouseStock::updateOrCreate(
            ['warehouse_id' => $this->id, 'product_id' => $productId],
            ['quantity' => 0]
        );

        $stock->quantity = max(0, $stock->quantity + $delta);
        $stock->save();

        return $stock;
    }
}
