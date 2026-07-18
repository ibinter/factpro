<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOcrJob;
use App\Models\OcrScan;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Services\LicenseService;
use App\Services\OcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OcrController extends Controller
{
    private const ALLOWED_PLANS = ['business', 'enterprise'];

    public function __construct(
        private LicenseService $licenses,
        private OcrService $ocr,
    ) {
    }

    private function hasAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && in_array($license->plan?->code, self::ALLOWED_PLANS, true);
    }

    private function guard(Request $request): void
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'Le module OCR est réservé aux forfaits BUSINESS et ENTERPRISE.'
        );
    }

    /** Page principale OCR — liste les scans du mois courant. */
    public function index(Request $request): Response
    {
        $hasAccess = $this->hasAccess($request);
        $user = $request->user();
        $companyId = $user->current_company_id;

        $scans = [];
        if ($hasAccess && $companyId) {
            $scans = OcrScan::forCompany($companyId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->latest()
                ->take(10)
                ->get()
                ->map(fn (OcrScan $s) => [
                    'id'                => $s->id,
                    'original_filename' => $s->original_filename,
                    'status'            => $s->status,
                    'extracted_data'    => $s->extracted_data,
                    'purchase_id'       => $s->purchase_id,
                    'created_at'        => $s->created_at?->toDateTimeString(),
                ]);
        }

        return Inertia::render('Purchases/OcrUpload', [
            'hasAccess' => $hasAccess,
            'scans'     => $scans,
        ]);
    }

    /** Upload un fichier PDF/image pour traitement OCR. */
    public function upload(Request $request): \Illuminate\Http\Response|JsonResponse
    {
        $this->guard($request);

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $file = $request->file('file');
        $storedName = Str::random(40).'.'.strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('ocr-scans', $storedName, 'private');

        $scan = OcrScan::create([
            'company_id'        => $request->user()->current_company_id,
            'user_id'           => $request->user()->id,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path'      => $path,
            'status'            => 'pending',
        ]);

        ProcessOcrJob::dispatch($scan);

        return response()->json([
            'id'     => $scan->id,
            'status' => $scan->status,
        ], 201);
    }

    /** Traitement synchrone d'un scan (fallback / re-traitement). */
    public function process(Request $request, OcrScan $scan): JsonResponse
    {
        $this->guard($request);
        $this->ensureScan($request, $scan);

        $scan->update(['status' => 'processing']);

        try {
            $fullPath = Storage::disk('private')->path($scan->storage_path);
            $rawText = $this->ocr->extractText($fullPath);
            $extracted = $this->ocr->parseInvoiceData($rawText);

            $scan->update([
                'ocr_raw_text'   => $rawText,
                'extracted_data' => $extracted,
                'status'         => 'done',
            ]);
        } catch (\Throwable $e) {
            $scan->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return response()->json($scan->fresh());
    }

    /** Convertit un scan OCR en facture d'achat (SupplierInvoice). */
    public function convert(Request $request, OcrScan $scan): RedirectResponse
    {
        $this->guard($request);
        $this->ensureScan($request, $scan);

        abort_if($scan->status !== 'done', 422, 'Le scan doit être traité avant conversion.');
        abort_if($scan->purchase_id !== null, 422, 'Ce scan a déjà été converti en achat.');

        $data = $request->validate([
            'supplier_id'   => ['required', 'integer'],
            'number'        => ['required', 'string', 'max:255'],
            'invoice_date'  => ['required', 'date'],
            'amount_ht'     => ['required', 'numeric', 'min:0'],
            'vat_amount'    => ['nullable', 'numeric', 'min:0'],
            'amount_ttc'    => ['required', 'numeric', 'min:0'],
            'category'      => ['required', 'string'],
        ]);

        $companyId = $request->user()->current_company_id;

        // Vérifie que le fournisseur appartient à la société.
        abort_unless(
            Supplier::where('company_id', $companyId)->whereKey($data['supplier_id'])->exists(),
            422,
            'Fournisseur introuvable.'
        );

        $invoice = SupplierInvoice::create([
            'company_id'   => $companyId,
            'created_by'   => $request->user()->id,
            'currency'     => $request->user()->currentCompany?->currency ?? 'XOF',
            'status'       => 'unpaid',
            'amount_paid'  => 0,
            'vat_amount'   => $data['vat_amount'] ?? 0,
            ...$data,
        ]);

        $scan->update(['purchase_id' => $invoice->id]);

        return redirect()->route('purchases.index')
            ->with('success', 'Facture créée depuis le scan OCR.');
    }

    private function ensureScan(Request $request, OcrScan $scan): void
    {
        abort_if(
            (int) $scan->company_id !== (int) $request->user()->current_company_id,
            404
        );
    }
}
