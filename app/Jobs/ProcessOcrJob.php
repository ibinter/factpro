<?php

namespace App\Jobs;

use App\Models\OcrScan;
use App\Services\OcrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessOcrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public OcrScan $scan)
    {
    }

    public function handle(OcrService $ocr): void
    {
        $this->scan->update(['status' => 'processing']);

        try {
            $disk = Storage::disk('private');
            $fullPath = $disk->path($this->scan->storage_path);

            $rawText = $ocr->extractText($fullPath);
            $extracted = $ocr->parseInvoiceData($rawText);

            $this->scan->update([
                'ocr_raw_text'   => $rawText,
                'extracted_data' => $extracted,
                'status'         => 'done',
            ]);
        } catch (\Throwable $e) {
            $this->scan->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
