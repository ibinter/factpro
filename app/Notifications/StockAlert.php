<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockAlert extends Notification
{
    use Queueable;

    public function __construct(private Product $product) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'stock_alert',
            'title' => "Stock bas â€” {$this->product->name}",
            'message' => "Stock actuel : {$this->product->stock_quantity} (seuil : {$this->product->stock_alert_threshold})",
            'url' => '/stock',
            'product_id' => $this->product->id,
            'icon' => 'âš ï¸',
        ];
    }
}
