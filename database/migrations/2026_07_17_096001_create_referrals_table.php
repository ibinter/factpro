<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Programme ambassadeur — table des parrainages (cahier IBIG §22 Phase 8).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_id')->nullable();
            $table->string('referral_code', 12);
            $table->enum('status', ['pending', 'converted', 'rewarded'])->default('pending');
            $table->tinyInteger('reward_months')->default(1);
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamps();

            $table->unique('referral_code');

            // FK seulement si non-SQLite (compatibilité tests :memory:)
            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('referrer_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('referred_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        // Coupons système ONG et École (insère si absents)
        if (Schema::hasTable('coupons')) {
            DB::table('coupons')->insertOrIgnore([
                [
                    'code'        => 'ONG50',
                    'description' => 'Offre spéciale ONG — 50% de réduction',
                    'type'        => 'percent',
                    'value'       => 50.00,
                    'applies_to'  => 'subscription',
                    'plan_code'   => null,
                    'max_redemptions' => null,
                    'redemptions_count' => 0,
                    'per_user_limit' => 1,
                    'min_amount'  => null,
                    'starts_at'   => null,
                    'expires_at'  => null,
                    'is_active'   => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'code'        => 'SCHOOL40',
                    'description' => 'Offre spéciale École — 40% de réduction',
                    'type'        => 'percent',
                    'value'       => 40.00,
                    'applies_to'  => 'subscription',
                    'plan_code'   => null,
                    'max_redemptions' => null,
                    'redemptions_count' => 0,
                    'per_user_limit' => 1,
                    'min_amount'  => null,
                    'starts_at'   => null,
                    'expires_at'  => null,
                    'is_active'   => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
