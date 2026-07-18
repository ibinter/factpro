<?php

use App\Models\Company;
use App\Models\PosSession;
use App\Models\PosTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeCashierUser(): array
{
    $user = createUserWithCompanyAndTrial();
    $company = Company::find($user->current_company_id);
    return [$user, $company];
}

function makePosSession(Company $company, User $user, array $extra = []): PosSession
{
    return PosSession::create(array_merge([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'status' => 'open',
        'opening_float' => 100,
        'opened_at' => now(),
        'tickets_count' => 0,
        'total_sales' => 0,
    ], $extra));
}

it('creates a pos table', function () {
    [$user, $company] = makeCashierUser();

    $this->actingAs($user)
        ->post(route('pos.tables.store'), [
            'name' => 'Table 1',
            'seats' => 4,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('pos_tables', [
        'company_id' => $company->id,
        'name' => 'Table 1',
        'seats' => 4,
        'status' => 'free',
    ]);
});

it('assigns an order to a table', function () {
    [$user, $company] = makeCashierUser();
    $session = makePosSession($company, $user);

    $table = PosTable::create([
        'company_id' => $company->id,
        'name' => 'Table 2',
        'seats' => 2,
    ]);

    $orderData = [
        ['description' => 'Pizza', 'quantity' => 1, 'unit_price' => 12.00],
        ['description' => 'Boisson', 'quantity' => 2, 'unit_price' => 3.50],
    ];

    $this->actingAs($user)
        ->postJson(route('pos.tables.order', $table->id), [
            'order_data' => $orderData,
            'session_id' => $session->id,
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $table->refresh();
    expect($table->status)->toBe('occupied');
    expect($table->order_data)->toHaveCount(2);
    expect($table->current_pos_session_id)->toBe($session->id);
});

it('frees a table', function () {
    [$user, $company] = makeCashierUser();

    $table = PosTable::create([
        'company_id' => $company->id,
        'name' => 'Table 3',
        'seats' => 4,
        'status' => 'occupied',
        'order_data' => [['description' => 'Plat', 'quantity' => 1, 'unit_price' => 15]],
    ]);

    $this->actingAs($user)
        ->postJson(route('pos.tables.free', $table->id))
        ->assertOk()
        ->assertJsonPath('success', true);

    $table->refresh();
    expect($table->status)->toBe('free');
    expect($table->order_data)->toBeNull();
});

it('refuses to delete an occupied table', function () {
    [$user, $company] = makeCashierUser();

    $table = PosTable::create([
        'company_id' => $company->id,
        'name' => 'Table 4',
        'seats' => 4,
        'status' => 'occupied',
    ]);

    $this->actingAs($user)
        ->delete(route('pos.tables.destroy', $table->id))
        ->assertRedirect();

    $this->assertDatabaseHas('pos_tables', ['id' => $table->id]);
});

it('isolates tables between companies', function () {
    [$user1, $company1] = makeCashierUser();
    [$user2, $company2] = makeCashierUser();

    $table = PosTable::create([
        'company_id' => $company2->id,
        'name' => 'Table VIP',
        'seats' => 6,
    ]);

    // user1 ne peut pas accéder à la table de company2
    $this->actingAs($user1)
        ->postJson(route('pos.tables.free', $table->id))
        ->assertForbidden();
});

it('opens pos session with cashier name', function () {
    [$user, $company] = makeCashierUser();

    $this->actingAs($user)
        ->post(route('pos.session.open'), [
            'opening_float' => 50,
            'cashier_name' => 'Marie',
            'cashier_pin' => '1234',
        ])
        ->assertRedirect();

    $session = PosSession::where('company_id', $company->id)->latest()->first();
    expect($session)->not->toBeNull();
    expect($session->cashier_name)->toBe('Marie');
    expect($session->cashier_pin)->not->toBeNull();
    // PIN doit être haché (bcrypt)
    expect(\Illuminate\Support\Facades\Hash::check('1234', $session->cashier_pin))->toBeTrue();
});

it('returns report x for open session', function () {
    [$user, $company] = makeCashierUser();
    $session = makePosSession($company, $user, [
        'cashier_name' => 'Jean',
        'tickets_count' => 3,
        'total_sales' => 150.00,
        'totals_by_method' => ['cash' => 100, 'card' => 50],
    ]);

    $this->actingAs($user)
        ->getJson(route('pos.session.reportX', $session->id))
        ->assertOk()
        ->assertJsonPath('session_id', $session->id)
        ->assertJsonPath('tickets_count', 3)
        ->assertJsonPath('total_sales', fn ($v) => (float) $v === 150.0)
        ->assertJsonPath('status', 'open');
});
