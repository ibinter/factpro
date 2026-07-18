<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Services\OutgoingWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function __construct(public WebhookDelivery $delivery)
    {
    }

    public function handle(OutgoingWebhookService $service): void
    {
        $service->sendDelivery($this->delivery);
    }
}
