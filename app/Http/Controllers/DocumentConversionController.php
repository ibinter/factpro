<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class DocumentConversionController extends Controller
{
    /**
     * Allowed conversion paths: source type => allowed target types
     */
    private array $allowedConversions = [
        'quote'         => ['order', 'invoice'],
        'order'         => ['delivery_note', 'invoice'],
        'delivery_note' => ['invoice'],
    ];

    public function convert(Request $request, Document $document)
    {
        $company = $request->user()->currentCompany;

        if ($company->id !== $document->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'target_type' => 'required|in:quote,order,delivery_note,invoice,purchase_order,credit_note',
        ]);

        $targetType = $validated['target_type'];
        $allowed = $this->allowedConversions[$document->type] ?? [];

        if (! in_array($targetType, $allowed, true)) {
            return back()->withErrors([
                'target_type' => "La conversion de '{$document->type}' vers '{$targetType}' n'est pas autorisée.",
            ]);
        }

        $newNumber = Document::generateNumber($company, $targetType);

        $newDoc = Document::create([
            'company_id'         => $document->company_id,
            'customer_id'        => $document->customer_id,
            'type'               => $targetType,
            'number'             => $newNumber,
            'status'             => 'draft',
            'currency'           => $document->currency,
            'issue_date'         => now()->toDateString(),
            'due_date'           => $document->due_date,
            'subtotal'           => $document->subtotal,
            'tax_amount'         => $document->tax_amount,
            'discount_amount'    => $document->discount_amount,
            'total'              => $document->total,
            'notes'              => $document->notes,
            'reference'          => $document->reference,
            'parent_document_id' => $document->id,
            'conversion_note'    => "Converti depuis {$document->number}",
        ]);

        foreach ($document->lines as $line) {
            $newDoc->lines()->create([
                'description' => $line->description,
                'quantity'    => $line->quantity,
                'unit_price'  => $line->unit_price,
                'tax_rate'    => $line->tax_rate,
                'discount'    => $line->discount,
                'line_total'  => $line->line_total,
            ]);
        }

        return redirect()->route('documents.show', $newDoc)
            ->with('success', 'Document converti avec succès');
    }

    public function chain(Request $request, Document $document): JsonResponse
    {
        $company = $request->user()->currentCompany;

        if ($company->id !== $document->company_id) {
            abort(403);
        }

        $chain = [];

        // Walk up to the root ancestor
        $root = $document;
        while ($root->parent_document_id !== null) {
            $parent = Document::find($root->parent_document_id);
            if (! $parent) {
                break;
            }
            $root = $parent;
        }

        // Collect all nodes in the chain (BFS from root)
        $visited = [];
        $queue   = [$root];

        while (! empty($queue)) {
            $current = array_shift($queue);

            if (in_array($current->id, $visited, true)) {
                continue;
            }
            $visited[] = $current->id;

            $chain[] = [
                'id'         => $current->id,
                'type'       => $current->type,
                'number'     => $current->number,
                'status'     => $current->status,
                'total'      => $current->total,
                'currency'   => $current->currency,
                'created_at' => $current->created_at,
            ];

            $children = Document::where('parent_document_id', $current->id)->get();
            foreach ($children as $child) {
                $queue[] = $child;
            }
        }

        return response()->json($chain);
    }
}
