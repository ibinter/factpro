<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhiteLabelConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WhiteLabelController extends Controller
{
    public function index(): Response
    {
        $configs = WhiteLabelConfig::with('ownerUser:id,name,email')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Admin/WhiteLabel', [
            'configs' => $configs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $config = WhiteLabelConfig::create($data);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('white-label-logos', 'private');
            $config->update(['logo_url' => $path]);
        }

        return back()->with('success', "Config white-label « {$config->app_name} » créée.");
    }

    public function update(WhiteLabelConfig $config, Request $request): RedirectResponse
    {
        $data = $this->validated($request, $config);

        $config->update($data);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('white-label-logos', 'private');
            $config->update(['logo_url' => $path]);
        }

        return back()->with('success', "Config « {$config->app_name} » mise à jour.");
    }

    public function destroy(WhiteLabelConfig $config): RedirectResponse
    {
        $name = $config->app_name;
        $config->delete();

        return back()->with('success', "Config « {$name} » supprimée.");
    }

    public function uploadLogo(Request $request, WhiteLabelConfig $config): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('logo')->store('white-label-logos', 'private');
        $config->update(['logo_url' => $path]);

        return back()->with('success', 'Logo mis à jour.');
    }

    private function validated(Request $request, ?WhiteLabelConfig $config = null): array
    {
        return $request->validate([
            'subdomain' => [
                'nullable', 'string', 'max:63',
                'regex:/^[a-z0-9\-]+$/i',
                Rule::unique('white_label_configs', 'subdomain')->ignore($config?->id),
            ],
            'app_name'        => ['required', 'string', 'max:100'],
            'primary_color'   => ['required', 'string', 'size:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'string', 'size:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color'    => ['required', 'string', 'size:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'footer_text'     => ['nullable', 'string', 'max:1000'],
            'support_email'   => ['nullable', 'email', 'max:255'],
            'is_active'       => ['boolean'],
            'owner_user_id'   => ['nullable', 'exists:users,id'],
        ]);
    }
}
