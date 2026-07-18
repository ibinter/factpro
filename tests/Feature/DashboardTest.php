<?php

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Product;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
});

// Helper : crée une facture finalisée pour la société
function dashInvoice(int $companyId, float $total, string $status = 'paid', ?string $issueDate = null, ?string $dueDate = null): Document
{
    return Document::create([
        'company_id' => $companyId,
        'type' => 'invoice',
        'number' => 'DASH-' . strtoupper(Str::random(8)),
        'status' => $status,
        'issue_date' => $issueDate ?? now()->toDateString(),
        'due_date' => $dueDate ?? now()->addDays(30)->toDateString(),
        'currency' => 'XOF',
        'subtotal' => $total,
        'tax_amount' => 0,
        'total' => $total,
        'amount_paid' => in_array($status, ['paid']) ? $total : 0,
        'finalized_at' => now(),
    ]);
}

// Helper : crée un devis
function dashQuote(int $companyId, string $status = 'sent'): Document
{
    return Document::create([
        'company_id' => $companyId,
        'type' => 'quote',
        'number' => 'DEV-' . strtoupper(Str::random(8)),
        'status' => $status,
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        'subtotal' => 100000,
        'tax_amount' => 0,
        'total' => 100000,
        'amount_paid' => 0,
        'finalized_at' => now(),
    ]);
}

it('returns dashboard page with new props', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('monthlyRevenue')
            ->has('topCustomers')
            ->has('topProducts')
            ->has('alerts')
            ->has('conversionRate')
        );
});

it('includes monthly revenue for last 12 months', function () {
    dashInvoice($this->company->id, 200000, 'paid', now()->subMonths(2)->toDateString());
    dashInvoice($this->company->id, 100000, 'paid', now()->toDateString());

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('monthlyRevenue', 12)
            ->where('monthlyRevenue', function ($months) {
                $months = collect($months)->map(fn($m) => (array) $m);
                // Each entry has required keys
                foreach ($months as $m) {
                    if (!array_key_exists('month', $m) || !array_key_exists('revenue', $m) || !array_key_exists('invoices_count', $m)) {
                        return false;
                    }
                }
                // Total revenue should be at least 300000
                return $months->sum('revenue') >= 300000;
            })
        );
});

it('includes top customers by revenue', function () {
    $customer1 = createCustomerFor($this->company, ['name' => 'Client A']);
    $customer2 = createCustomerFor($this->company, ['name' => 'Client B']);

    $doc1 = dashInvoice($this->company->id, 500000, 'paid');
    $doc1->update(['customer_id' => $customer1->id]);

    $doc2 = dashInvoice($this->company->id, 300000, 'paid');
    $doc2->update(['customer_id' => $customer2->id]);

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('topCustomers', function ($customers) {
                if (empty($customers)) return false;
                $first = $customers[0];
                return isset($first['name'], $first['total'], $first['invoices_count'])
                    && $first['total'] >= 500000;
            })
        );
});

it('includes top products by quantity', function () {
    $doc = dashInvoice($this->company->id, 200000, 'paid');

    DocumentLine::create([
        'document_id' => $doc->id,
        'description' => 'Prestation Web',
        'quantity' => 10,
        'unit_price' => 10000,
        'tax_rate' => 0,
        'line_total' => 100000,
    ]);
    DocumentLine::create([
        'document_id' => $doc->id,
        'description' => 'Hébergement',
        'quantity' => 5,
        'unit_price' => 20000,
        'tax_rate' => 0,
        'line_total' => 100000,
    ]);

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('topProducts', function ($products) {
                if (empty($products)) return false;
                $first = $products[0];
                return isset($first['name'], $first['quantity'], $first['revenue'])
                    && $first['name'] === 'Prestation Web'
                    && $first['quantity'] >= 10;
            })
        );
});

it('includes active alerts when overdue invoices exist', function () {
    // Facture en retard de plus de 7 jours
    dashInvoice($this->company->id, 150000, 'overdue',
        now()->subDays(30)->toDateString(),
        now()->subDays(15)->toDateString()
    );

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('alerts', function ($alerts) {
                return collect($alerts)->contains('type', 'invoices_overdue');
            })
        );
});

it('returns conversion rate for quotes', function () {
    // 2 devis dont 1 converti
    dashQuote($this->company->id, 'sent');
    dashQuote($this->company->id, 'converted');

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('conversionRate', fn ($rate) => (float) $rate === 50.0)
        );
});

it('returns zero conversion rate when no quotes exist', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('conversionRate', fn ($rate) => (float) $rate === 0.0)
        );
});

it('includes large unpaid invoice alert', function () {
    dashInvoice($this->company->id, 600000, 'sent');

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('alerts', function ($alerts) {
                return collect($alerts)->contains('type', 'large_unpaid');
            })
        );
});
