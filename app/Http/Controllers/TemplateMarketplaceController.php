<?php

namespace App\Http\Controllers;

use App\Models\TemplateMarketplace;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TemplateMarketplaceController extends Controller
{
    private const BUSINESS_PLANS = ['business', 'enterprise'];

    public function __construct(private LicenseService $licenses) {}

    private function hasBusinessPlan(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && in_array($license->plan?->code, self::BUSINESS_PLANS, true);
    }

    private function isSuperadmin(Request $request): bool
    {
        return (bool) $request->user()?->is_superadmin;
    }

    public function index(Request $request): Response
    {
        $companyId = $request->user()->current_company_id;

        $community = TemplateMarketplace::public()
            ->with(['user:id,name', 'company:id,name'])
            ->latest()
            ->get()
            ->map(fn ($t) => $this->formatTemplate($t));

        $mine = TemplateMarketplace::forCompany($companyId)
            ->with(['user:id,name'])
            ->latest()
            ->get()
            ->map(fn ($t) => $this->formatTemplate($t));

        return Inertia::render('Templates/Marketplace', [
            'community' => $community,
            'mine' => $mine,
            'systemTemplates' => array_map(
                fn ($key, $cfg) => array_merge($cfg, ['key' => $key]),
                array_keys(config('pdf_templates')),
                array_values(config('pdf_templates'))
            ),
            'canShare' => $this->hasBusinessPlan($request),
        ]);
    }

    public function myTemplates(Request $request): Response
    {
        $companyId = $request->user()->current_company_id;

        $templates = TemplateMarketplace::forCompany($companyId)
            ->latest()
            ->get()
            ->map(fn ($t) => $this->formatTemplate($t));

        return Inertia::render('Templates/Marketplace', [
            'community' => [],
            'mine' => $templates,
            'systemTemplates' => [],
            'canShare' => $this->hasBusinessPlan($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->hasBusinessPlan($request)) {
            abort(403, 'Réservé aux forfaits Business et Enterprise.');
        }

        $data = $request->validate([
            'base_template' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'custom_css' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'is_public' => ['boolean'],
        ]);

        TemplateMarketplace::create([
            ...$data,
            'company_id' => $request->user()->current_company_id,
            'user_id' => $request->user()->id,
            'is_approved' => false,
        ]);

        return redirect()->route('marketplace.index')->with('success', 'Template soumis. Il sera visible après modération.');
    }

    public function update(TemplateMarketplace $template, Request $request): RedirectResponse
    {
        $user = $request->user();

        if ((int) $template->company_id !== (int) $user->current_company_id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'primary_color' => ['sometimes', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['sometimes', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['sometimes', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'custom_css' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'is_public' => ['boolean'],
        ]);

        // Resoumettre à modération si modifié
        if ($template->is_approved && isset($data['is_public'])) {
            $data['is_approved'] = false;
        }

        $template->update($data);

        return redirect()->route('marketplace.index')->with('success', 'Template mis à jour.');
    }

    public function destroy(TemplateMarketplace $template, Request $request): RedirectResponse
    {
        $user = $request->user();

        $isOwner = (int) $template->company_id === (int) $user->current_company_id;
        $isSuperadmin = $this->isSuperadmin($request);

        if (! $isOwner && ! $isSuperadmin) {
            abort(403);
        }

        $template->delete();

        return redirect()->route('marketplace.index')->with('success', 'Template supprimé.');
    }

    public function download(TemplateMarketplace $template, Request $request)
    {
        $template->increment('downloads_count');

        return response()->json([
            'template' => $this->formatTemplate($template),
            'config' => $template->toTemplateConfig(),
        ]);
    }

    public function rate(TemplateMarketplace $template, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $template->increment('rating_sum', $data['rating']);
        $template->increment('rating_count');

        return redirect()->route('marketplace.index')->with('success', 'Note enregistrée.');
    }

    public function approve(TemplateMarketplace $template, Request $request): RedirectResponse
    {
        if (! $this->isSuperadmin($request)) {
            abort(403);
        }

        $template->update(['is_approved' => true]);

        return redirect()->route('marketplace.index')->with('success', 'Template approuvé.');
    }

    private function formatTemplate(TemplateMarketplace $t): array
    {
        return [
            'id' => $t->id,
            'name' => $t->name,
            'description' => $t->description,
            'base_template' => $t->base_template,
            'primary_color' => $t->primary_color,
            'secondary_color' => $t->secondary_color,
            'accent_color' => $t->accent_color,
            'custom_css' => $t->custom_css,
            'tags' => $t->tags ?? [],
            'is_public' => $t->is_public,
            'is_approved' => $t->is_approved,
            'downloads_count' => $t->downloads_count,
            'average_rating' => $t->averageRating(),
            'rating_count' => $t->rating_count,
            'company' => $t->relationLoaded('company') ? $t->company?->only(['id', 'name']) : null,
            'user' => $t->relationLoaded('user') ? $t->user?->only(['id', 'name']) : null,
        ];
    }
}
