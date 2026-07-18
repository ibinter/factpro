<?php

use App\Models\User;
use App\Models\WhiteLabelConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSuperadmin(): User
{
    return User::factory()->create([
        'email' => 'admin@ibigsoft.com',
        'is_superadmin' => true,
    ]);
}

function makeRegularUser(): User
{
    return User::factory()->create(['is_superadmin' => false]);
}

it('creates a white label config', function () {
    $admin = makeSuperadmin();

    $response = $this->actingAs($admin)->post(route('admin.white-label.store'), [
        'subdomain'       => 'clientx',
        'app_name'        => 'ClientX Suite',
        'primary_color'   => '#FF0000',
        'secondary_color' => '#000000',
        'accent_color'    => '#FFFF00',
        'footer_text'     => '© 2026 ClientX',
        'support_email'   => 'support@clientx.com',
        'is_active'       => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('white_label_configs', [
        'subdomain' => 'clientx',
        'app_name'  => 'ClientX Suite',
    ]);
});

it('only allows superadmin', function () {
    $user = makeRegularUser();

    $response = $this->actingAs($user)->post(route('admin.white-label.store'), [
        'subdomain'       => 'hacker',
        'app_name'        => 'Hack App',
        'primary_color'   => '#FF0000',
        'secondary_color' => '#000000',
        'accent_color'    => '#FFFF00',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('white_label_configs', ['subdomain' => 'hacker']);
});

it('updates white label config', function () {
    $admin = makeSuperadmin();
    $config = WhiteLabelConfig::create([
        'subdomain'       => 'testclient',
        'app_name'        => 'Test App',
        'primary_color'   => '#0062CC',
        'secondary_color' => '#002D5B',
        'accent_color'    => '#F0C040',
        'is_active'       => true,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.white-label.update', $config->id), [
        'app_name'        => 'Test App Updated',
        'primary_color'   => '#123456',
        'secondary_color' => '#654321',
        'accent_color'    => '#ABCDEF',
        'is_active'       => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('white_label_configs', [
        'id'       => $config->id,
        'app_name' => 'Test App Updated',
    ]);
});

it('resolves config by subdomain', function () {
    WhiteLabelConfig::create([
        'subdomain'       => 'myreseller',
        'app_name'        => 'MyReseller App',
        'primary_color'   => '#0062CC',
        'secondary_color' => '#002D5B',
        'accent_color'    => '#F0C040',
        'is_active'       => true,
    ]);

    $request = \Illuminate\Http\Request::create('http://myreseller.ibigfactpro.com/dashboard');
    $request->server->set('HTTP_HOST', 'myreseller.ibigfactpro.com');

    $config = WhiteLabelConfig::forRequest($request);

    expect($config)->not->toBeNull();
    expect($config->app_name)->toBe('MyReseller App');
});

it('returns null config for main domain', function () {
    $request = \Illuminate\Http\Request::create('http://ibigfactpro.com/dashboard');
    $request->server->set('HTTP_HOST', 'ibigfactpro.com');

    $config = WhiteLabelConfig::forRequest($request);

    expect($config)->toBeNull();
});

it('deletes a config', function () {
    $admin = makeSuperadmin();
    $config = WhiteLabelConfig::create([
        'subdomain'       => 'todelete',
        'app_name'        => 'To Delete',
        'primary_color'   => '#0062CC',
        'secondary_color' => '#002D5B',
        'accent_color'    => '#F0C040',
        'is_active'       => true,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.white-label.destroy', $config->id));

    $response->assertRedirect();
    $this->assertDatabaseMissing('white_label_configs', ['id' => $config->id]);
});
