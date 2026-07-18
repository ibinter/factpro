<?php

use App\Models\License;
use App\Models\Referral;
use App\Models\User;
use App\Services\ReferralService;

/*
|--------------------------------------------------------------------------
| Tests — Programme ambassadeur / parrainage (cahier IBIG §22 Phase 8)
|--------------------------------------------------------------------------
*/

it('generates a unique referral code per user', function () {
    $user = createUserWithCompanyAndTrial();

    $service = app(ReferralService::class);
    $code    = $service->getOrCreateCode($user);

    expect($code)->toMatch('/^IBG-[A-Z0-9]{6}$/');
    expect($user->fresh()->referral_code)->toBe($code);

    // Idempotent : appel répété retourne le même code
    expect($service->getOrCreateCode($user->fresh()))->toBe($code);
});

it('registers a referral on signup with ref code', function () {
    $referrer = createUserWithCompanyAndTrial();

    $service = app(ReferralService::class);
    $code    = $service->getOrCreateCode($referrer);

    $newUser = createUserWithCompanyAndTrial();

    $service->registerReferral($newUser, $code);

    expect(Referral::where('referrer_id', $referrer->id)
        ->where('referred_id', $newUser->id)
        ->where('status', 'pending')
        ->exists()
    )->toBeTrue();

    expect($newUser->fresh()->referred_by_id)->toBe($referrer->id);
});

it('rewards referrer with license extension on subscription', function () {
    $referrer = createUserWithCompanyAndTrial();
    $referred = createUserWithCompanyAndTrial();

    $service = app(ReferralService::class);
    $code    = $service->getOrCreateCode($referrer);

    $service->registerReferral($referred, $code);

    // Récupère la licence du parrain avant récompense
    $licenseBefore = $referrer->licenses()->first();
    $endsBefore    = $licenseBefore->ends_at->copy();

    $service->rewardReferrer($referred->fresh());

    $referral = Referral::where('referrer_id', $referrer->id)
        ->where('referred_id', $referred->id)
        ->first();

    expect($referral->status)->toBe('rewarded');
    expect($referral->rewarded_at)->not->toBeNull();

    $endsAfter = $referrer->licenses()->first()->ends_at;
    expect($endsAfter->gt($endsBefore))->toBeTrue();
});

it('does not reward twice for same referral', function () {
    $referrer = createUserWithCompanyAndTrial();
    $referred = createUserWithCompanyAndTrial();

    $service = app(ReferralService::class);
    $code    = $service->getOrCreateCode($referrer);

    $service->registerReferral($referred, $code);

    // Première récompense
    $service->rewardReferrer($referred->fresh());

    $licenseAfterFirst = $referrer->licenses()->first()->ends_at->copy();

    // Deuxième tentative (ne doit rien faire)
    $service->rewardReferrer($referred->fresh());

    $licenseAfterSecond = $referrer->licenses()->first()->ends_at;

    expect($licenseAfterSecond->equalTo($licenseAfterFirst))->toBeTrue();
    expect(Referral::where('referrer_id', $referrer->id)->where('status', 'rewarded')->count())->toBe(1);
});

it('returns referral stats for user', function () {
    $referrer = createUserWithCompanyAndTrial();
    $referred = createUserWithCompanyAndTrial();

    $service = app(ReferralService::class);
    $code    = $service->getOrCreateCode($referrer);

    $service->registerReferral($referred, $code);
    $service->rewardReferrer($referred->fresh());

    $stats = $service->getStats($referrer->fresh());

    expect($stats['code'])->toBe($code);
    expect($stats['total'])->toBe(1);
    expect($stats['rewarded'])->toBe(1);
    expect($stats['months_earned'])->toBe(1);
    expect($stats['link'])->toContain('?ref=' . $code);
});
