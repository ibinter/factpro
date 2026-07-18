<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Coupons & réductions sur l'abonnement (cahier IBIG §22.2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // stocké en MAJUSCULES
            $table->string('description')->nullable();
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->decimal('value', 15, 2);
            $table->string('applies_to', 30)->default('subscription'); // extensible
            $table->string('plan_code')->nullable(); // restreint à un forfait
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('redemptions_count')->default(0);
            $table->unsignedTinyInteger('per_user_limit')->default(1);
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->date('starts_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('company_id')->nullable()->constrained();
            $table->uuid('order_id')->nullable();
            $table->decimal('amount_discounted', 15, 2)->default(0);
            $table->dateTime('redeemed_at');
            $table->timestamps();

            $table->index(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupons');
    }
};
