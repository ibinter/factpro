<?php

use App\Models\Document;
use App\Models\PosSession;
use App\Models\Product;

/** Crée une session de caisse ouverte pour un utilisateur. */
function openPosSessionFor($user, float $openingFloat = 5000, array $attributes = []): PosSession
{
    return PosSession::create([
        'company_id' => $user->current_company_id,
        'user_id' => $user->id,
        'status' => 'open',
        'opening_float' => $openingFloat,
        'opened_at' => now(),
        ...$attributes,
    ]);
}

/** Crée un produit vendable pour la société de l'utilisateur. */
function createPosProduct($user, array $attributes = []): Product
{
    return Product::create([
        'company_id' => $user->current_company_id,
        'type' => 'product',
        'name' => 'Produit Caisse',
        'unit' => 'unité',
        'price' => 1000,
        'tax_rate' => 0,
        'is_active' => true,
        ...$attributes,
    ]);
}

it('ouvre une session de caisse avec fonds initial', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->post(route('pos.session.open'), ['opening_float' => 10000])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('pos_sessions', [
        'company_id' => $user->current_company_id,
        'user_id' => $user->id,
        'status' => 'open',
        'opening_float' => 10000,
    ]);
});

it('refuse une seconde session ouverte pour le même caissier', function () {
    $user = createUserWithCompanyAndTrial();
    openPosSessionFor($user);

    $this->actingAs($user)
        ->post(route('pos.session.open'), ['opening_float' => 2000])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(PosSession::where('user_id', $user->id)->count())->toBe(1);
});

it('encaisse une vente : ticket pos_ticket finalisé, payé, et session mise à jour', function () {
    $user = createUserWithCompanyAndTrial();
    $session = openPosSessionFor($user, 5000);
    $product = createPosProduct($user, ['price' => 1000, 'tax_rate' => 0]);

    $this->actingAs($user)
        ->post(route('pos.checkout'), [
            'customer_id' => null,
            'lines' => [[
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => 2,
                'unit' => 'unité',
                'unit_price' => 1000,
                'discount_percent' => 0,
                'tax_rate' => 0,
            ]],
            'payments' => [['method' => 'cash', 'amount' => 2000]],
            'received' => 5000,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $ticket = Document::where('company_id', $user->current_company_id)
        ->where('type', 'pos_ticket')
        ->first();

    expect($ticket)->not->toBeNull()
        ->and($ticket->isFinalized())->toBeTrue()
        ->and($ticket->status)->toBe('paid')
        ->and((float) $ticket->total)->toBe(2000.0)
        ->and((float) $ticket->amount_paid)->toBe(2000.0)
        ->and($ticket->reference)->toBe('POS-S'.$session->id);

    $session->refresh();
    expect($session->tickets_count)->toBe(1)
        ->and((float) $session->total_sales)->toBe(2000.0)
        ->and((float) ($session->totals_by_method['cash'] ?? 0))->toBe(2000.0);
});

it('refuse l\'encaissement sans session de caisse ouverte', function () {
    $user = createUserWithCompanyAndTrial();
    $product = createPosProduct($user);

    $this->actingAs($user)
        ->post(route('pos.checkout'), [
            'lines' => [[
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_rate' => 0,
            ]],
            'payments' => [['method' => 'cash', 'amount' => 1000]],
        ])
        ->assertSessionHasErrors('session');

    expect(Document::where('type', 'pos_ticket')->count())->toBe(0);
});

it('refuse un encaissement dont les paiements ne couvrent pas le total', function () {
    $user = createUserWithCompanyAndTrial();
    openPosSessionFor($user);
    $product = createPosProduct($user, ['price' => 5000]);

    $this->actingAs($user)
        ->post(route('pos.checkout'), [
            'lines' => [[
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => 1,
                'unit_price' => 5000,
                'tax_rate' => 0,
            ]],
            'payments' => [['method' => 'cash', 'amount' => 1000]],
        ])
        ->assertSessionHasErrors('payments');

    // La transaction est annulée : aucun ticket créé
    expect(Document::where('type', 'pos_ticket')->count())->toBe(0);
});

it('clôture la session : espèces attendues, écart et rapport Z', function () {
    $user = createUserWithCompanyAndTrial();
    $session = openPosSessionFor($user, 5000, [
        'tickets_count' => 3,
        'total_sales' => 20000,
        'totals_by_method' => ['cash' => 15000, 'mobile_money' => 5000],
    ]);

    $this->actingAs($user)
        ->post(route('pos.session.close'), [
            'counted_cash' => 19000,
            'notes' => 'Billet de 1000 manquant.',
        ])
        ->assertRedirect(route('pos.report', $session));

    $session->refresh();
    expect($session->status)->toBe('closed')
        ->and((float) $session->expected_cash)->toBe(20000.0) // 5000 fonds + 15000 espèces
        ->and((float) $session->counted_cash)->toBe(19000.0)
        ->and((float) $session->difference)->toBe(-1000.0)
        ->and($session->closed_at)->not->toBeNull();

    // Le rapport Z est accessible
    $this->actingAs($user)
        ->get(route('pos.report', $session))
        ->assertOk();
});

it('interdit le rapport Z d\'une session d\'une autre société', function () {
    $user = createUserWithCompanyAndTrial();
    $other = createUserWithCompanyAndTrial();
    $session = openPosSessionFor($other);

    $this->actingAs($user)
        ->get(route('pos.report', $session))
        ->assertForbidden();
});
