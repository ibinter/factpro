<?php

use App\Models\StatusIncident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSuperadminForStatus(): User
{
    return User::factory()->create(['is_superadmin' => true]);
}

function makeUserForStatus(): User
{
    return User::factory()->create(['is_superadmin' => false]);
}

it('status page is accessible without authentication', function () {
    $response = $this->get(route('status.public'));
    $response->assertStatus(200);
});

it('status page shows all components', function () {
    $response = $this->get(route('status.public'));
    $response->assertInertia(fn ($page) =>
        $page->component('Status/Public')
             ->has('components', 7)
    );
});

it('status page shows operational when no incidents', function () {
    $response = $this->get(route('status.public'));
    $response->assertInertia(fn ($page) =>
        $page->component('Status/Public')
             ->where('incidents', [])
    );
});

it('status api returns json', function () {
    $response = $this->get(route('status.api'));
    $response->assertStatus(200)
             ->assertJsonStructure(['status', 'updated_at', 'components', 'incidents']);
});

it('creates an incident', function () {
    $admin = makeSuperadminForStatus();

    $response = $this->actingAs($admin)->post(route('admin.incidents.store'), [
        'title'               => 'API lente',
        'description'         => 'Les réponses API prennent plus de 5s.',
        'severity'            => 'major',
        'status'              => 'investigating',
        'affected_components' => ['api'],
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('status_incidents', ['title' => 'API lente']);
});

it('active incident marks component as degraded', function () {
    StatusIncident::create([
        'title'               => 'Problème POS',
        'description'         => 'Caisse hors ligne.',
        'severity'            => 'minor',
        'status'              => 'investigating',
        'affected_components' => ['pos'],
        'started_at'          => now(),
        'is_public'           => true,
    ]);

    $response = $this->get(route('status.public'));
    $response->assertInertia(fn ($page) =>
        $page->component('Status/Public')
             ->where('components.4.status', 'degraded')
    );
});

it('resolves an incident', function () {
    $admin = makeSuperadminForStatus();
    $incident = StatusIncident::create([
        'title'       => 'Incident test',
        'description' => 'Test.',
        'severity'    => 'minor',
        'status'      => 'investigating',
        'started_at'  => now(),
    ]);

    $response = $this->actingAs($admin)->post(route('admin.incidents.resolve', $incident));
    $response->assertStatus(200);

    $this->assertDatabaseHas('status_incidents', [
        'id'     => $incident->id,
        'status' => 'resolved',
    ]);
});

it('ops board requires superadmin', function () {
    $user = makeUserForStatus();
    $response = $this->actingAs($user)->get(route('admin.ops-board'));
    $response->assertStatus(403);
});

it('resolved incidents appear in history', function () {
    StatusIncident::create([
        'title'       => 'Panne résolue',
        'description' => 'Panne de facturation.',
        'severity'    => 'major',
        'status'      => 'resolved',
        'started_at'  => now()->subHours(2),
        'resolved_at' => now()->subHour(),
        'is_public'   => true,
    ]);

    $response = $this->get(route('status.public'));
    $response->assertInertia(fn ($page) =>
        $page->component('Status/Public')
             ->has('resolved', 1)
    );
});

it('status api includes component statuses', function () {
    $response = $this->get(route('status.api'));
    $response->assertStatus(200)
             ->assertJsonStructure([
                 'status',
                 'components' => [
                     '*' => ['name', 'key', 'status'],
                 ],
             ]);
});
