<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Company $company,
        private readonly string  $type,        // 'customers' | 'products'
        private readonly array   $rows,
        private readonly array   $columnMap,
    ) {}

    public function handle(ImportService $service): void
    {
        if ($this->type === 'customers') {
            $service->importCustomers($this->company, $this->rows, $this->columnMap);
        } else {
            $service->importProducts($this->company, $this->rows, $this->columnMap);
        }
    }
}
