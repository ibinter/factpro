<?php

use App\Mail\InvoiceReminderMail;
use App\Models\Company;
use App\Models\Document;
use App\Models\ReminderLog;
use App\Services\ReminderService;
use Illuminate\Support\Facades\Mail;

/** Crée une facture impayée échue il y a $daysLate jours, client avec email. */
function createOverdueInvoice(Company $company, int $daysLate, array $attributes = []): Document
{
    $customer = createCustomerFor($company, ['email' => 'client@example.test']);

    return Document::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'type' => 'invoice',
        'number' => 'FAC-TEST-'.strtoupper(\Illuminate\Support\Str::random(8)),
        'status' => 'sent',
        'issue_date' => now()->subDays($daysLate + 30)->toDateString(),
        'due_date' => now()->subDays($daysLate)->toDateString(),
        'currency' => 'XOF',
        'subtotal' => 100000,
        'total' => 100000,
        'amount_paid' => 0,
        ...$attributes,
    ]);
}

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
});

it('marks an expired unpaid invoice as overdue via invoices:mark-overdue', function () {
    $invoice = createOverdueInvoice($this->company, 5);
    $paid = createOverdueInvoice($this->company, 5, ['amount_paid' => 100000, 'number' => 'FAC-PAID']);
    $notDue = createOverdueInvoice($this->company, 0, ['due_date' => now()->addDays(10)->toDateString(), 'number' => 'FAC-FUTUR']);

    $this->artisan('invoices:mark-overdue')->assertSuccessful();

    expect($invoice->fresh()->status)->toBe('overdue')
        ->and($paid->fresh()->status)->toBe('sent')
        ->and($notDue->fresh()->status)->toBe('sent');
});

it('sends the reminder level matching the actual lateness via runAuto', function () {
    Mail::fake();

    $j4 = createOverdueInvoice($this->company, 4);   // J+4  → niveau 1 (seuil 3 j)
    $j10 = createOverdueInvoice($this->company, 10); // J+10 → niveau 2 direct (seuil 7 j), sans rafale de rattrapage
    $j1 = createOverdueInvoice($this->company, 1);   // J+1  → aucun seuil atteint

    $sent = app(ReminderService::class)->runAuto($this->company);

    expect($sent)->toBe(2)
        ->and(ReminderLog::where('document_id', $j4->id)->pluck('level')->all())->toBe([1])
        ->and(ReminderLog::where('document_id', $j10->id)->pluck('level')->all())->toBe([2])
        ->and(ReminderLog::where('document_id', $j1->id)->count())->toBe(0)
        ->and($j4->fresh()->status)->toBe('overdue')
        ->and($j10->fresh()->status)->toBe('overdue');

    Mail::assertSent(InvoiceReminderMail::class, 2);
    Mail::assertSent(InvoiceReminderMail::class, fn ($mail) => $mail->document->is($j10) && $mail->level === 2);
});

it('is idempotent: two runAuto executions send only one reminder per level', function () {
    Mail::fake();

    $invoice = createOverdueInvoice($this->company, 8); // J+8 → niveau 2

    $service = app(ReminderService::class);
    $first = $service->runAuto($this->company);
    $second = $service->runAuto($this->company);

    expect($first)->toBe(1)
        ->and($second)->toBe(0)
        ->and(ReminderLog::where('document_id', $invoice->id)->count())->toBe(1);

    Mail::assertSent(InvoiceReminderMail::class, 1);
});

it('escalates to the next level on later runs when the threshold is reached', function () {
    Mail::fake();

    $invoice = createOverdueInvoice($this->company, 20); // J+20 → niveau 3 direct

    $service = app(ReminderService::class);
    $service->runAuto($this->company);

    expect(ReminderLog::where('document_id', $invoice->id)->pluck('level')->all())->toBe([3])
        ->and($service->nextLevelFor($invoice))->toBeNull(); // plus de relance possible

    expect($service->runAuto($this->company))->toBe(0);
});

it('allows a manual reminder from the interface (POST reminders.send)', function () {
    Mail::fake();

    $invoice = createOverdueInvoice($this->company, 2);

    $response = $this->actingAs($this->user)
        ->from(route('reminders.index'))
        ->post(route('reminders.send', $invoice));

    $response->assertRedirect(route('reminders.index'));
    $response->assertSessionHas('success');

    $log = ReminderLog::where('document_id', $invoice->id)->firstOrFail();

    expect($log->level)->toBe(1)
        ->and($log->triggered_by)->toBe('manual')
        ->and($log->sent_by)->toBe($this->user->id)
        ->and($invoice->fresh()->status)->toBe('overdue');

    Mail::assertSent(InvoiceReminderMail::class, 1);
});

it('forbids sending a manual reminder for another company invoice', function () {
    $stranger = createUserWithCompanyAndTrial();
    $foreign = createOverdueInvoice($stranger->currentCompany, 5);

    $this->actingAs($this->user)
        ->post(route('reminders.send', $foreign))
        ->assertForbidden();
});

it('saves reminder settings (enabled + custom thresholds)', function () {
    $response = $this->actingAs($this->user)
        ->from(route('reminders.index'))
        ->patch(route('reminders.settings'), [
            'enabled' => false,
            'levels' => [1 => 5, 2 => 10, 3 => 20],
        ]);

    $response->assertRedirect(route('reminders.index'));
    $response->assertSessionHas('success');

    $company = $this->company->fresh();
    $service = app(ReminderService::class);

    expect($service->isEnabled($company))->toBeFalse()
        ->and($service->levels($company)[2]['days'])->toBe(10)
        ->and($service->levels($company)[3]['days'])->toBe(20);
});

it('rejects non-increasing reminder thresholds', function () {
    $this->actingAs($this->user)
        ->from(route('reminders.index'))
        ->patch(route('reminders.settings'), [
            'enabled' => true,
            'levels' => [1 => 10, 2 => 7, 3 => 15],
        ])
        ->assertSessionHasErrors('levels');
});
