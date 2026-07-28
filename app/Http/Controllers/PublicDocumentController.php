<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PublicDocumentController extends Controller
{
    /**
     * Page Inertia publique d'un document (sans authentification).
     *
     * URL : GET /doc/{token}
     * Vue : Public/Document.vue
     */
    public function show(string $token): Response
    {
        /** @var Document $document */
        $document = Document::with(['lines', 'customer', 'company'])
            ->where('public_token', $token)
            ->firstOrFail();

        abort_unless($document->isFinalized(), 404);

        // Marque un devis envoyé comme "vu" lors de la première consultation
        if ($document->type === 'quote' && $document->status === 'sent') {
            $document->update(['status' => 'viewed', 'viewed_at' => now()]);
        }

        $company  = $document->company;
        $customer = $document->customer;

        return Inertia::render('Public/Document', [
            'token'    => $token,
            'company'  => [
                'name'            => $company->name,
                'logo_path'       => $company->logo_path,
                'phone'           => $company->phone,
                'email'           => $company->email,
                'payment_methods' => $company->payment_methods ?? [],
            ],
            'customer' => $customer ? ['name' => $customer->name] : null,
            'document' => [
                'uuid'            => $document->uuid,
                'type_label'      => $document->type_label,
                'number'          => $document->number,
                'status'          => $document->status,
                'issue_date'      => $document->issue_date?->toDateString(),
                'due_date'        => $document->due_date?->toDateString(),
                'currency'        => $document->currency,
                'subtotal'        => (float) $document->subtotal,
                'discount_amount' => (float) $document->discount_amount,
                'tax_amount'      => (float) $document->tax_amount,
                'total'           => (float) $document->total,
                'amount_paid'     => (float) $document->amount_paid,
                'balance_due'     => (float) $document->balance_due,
                'created_at'      => $document->created_at,
                'sent_at'         => $document->sent_at,
                'viewed_at'       => $document->viewed_at,
                'paid_at'         => $document->paid_at,
                'lines'           => $document->lines->map(fn ($l) => [
                    'id'               => $l->id,
                    'description'      => $l->description,
                    'unit'             => $l->unit,
                    'quantity'         => (float) $l->quantity,
                    'unit_price'       => (float) $l->unit_price,
                    'discount_percent' => (float) $l->discount_percent,
                    'line_total'       => (float) $l->line_total,
                ])->values(),
            ],
        ]);
    }
}
