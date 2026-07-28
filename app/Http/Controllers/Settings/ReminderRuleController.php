<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ReminderRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReminderRuleController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $rules = ReminderRule::where('company_id', $company->id)
            ->orderBy('days_after_due')
            ->get();

        return Inertia::render('Settings/ReminderRules', [
            'rules' => $rules,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'days_after_due'   => ['required', 'integer', 'min:1', 'max:365'],
            'channel'          => ['required', 'in:email,whatsapp'],
            'message_template' => ['nullable', 'string', 'max:2000'],
            'is_active'        => ['boolean'],
        ]);

        ReminderRule::create([...$data, 'company_id' => $company->id]);

        return back()->with('success', 'Règle de rappel créée.');
    }

    public function update(Request $request, ReminderRule $reminderRule): RedirectResponse
    {
        $this->authorizeRule($request, $reminderRule);

        $data = $request->validate([
            'days_after_due'   => ['required', 'integer', 'min:1', 'max:365'],
            'channel'          => ['required', 'in:email,whatsapp'],
            'message_template' => ['nullable', 'string', 'max:2000'],
            'is_active'        => ['boolean'],
        ]);

        $reminderRule->update($data);

        return back()->with('success', 'Règle de rappel mise à jour.');
    }

    public function destroy(Request $request, ReminderRule $reminderRule): RedirectResponse
    {
        $this->authorizeRule($request, $reminderRule);

        $reminderRule->delete();

        return back()->with('success', 'Règle de rappel supprimée.');
    }

    private function authorizeRule(Request $request, ReminderRule $rule): void
    {
        $company = $request->user()->currentCompany;

        abort_unless($rule->company_id === $company->id, 403);
    }
}
