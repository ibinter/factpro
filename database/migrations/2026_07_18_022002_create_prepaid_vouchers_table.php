<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prepaid_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // Ex: IBIG-XXXX-XXXX-XXXX
            $table->string('batch_ref', 50)->nullable(); // Référence du lot (ex: BATCH-2026-001)
            $table->unsignedBigInteger('plan_id')->nullable(); // Forfait spécifique ou null (tous)
            $table->integer('duration_months')->default(1); // Durée en mois
            $table->string('currency', 10)->default('XOF');
            $table->decimal('face_value', 15, 2)->default(0); // Valeur nominale
            $table->decimal('reseller_price', 15, 2)->default(0); // Prix revendeur
            $table->string('reseller_name', 150)->nullable(); // Revendeur autorisé
            $table->enum('status', ['available', 'reserved', 'used', 'expired', 'cancelled'])->default('available');
            $table->unsignedBigInteger('used_by_user_id')->nullable();
            $table->unsignedBigInteger('used_by_company_id')->nullable();
            $table->char('activated_license_id', 36)->nullable(); // UUID
            $table->char('order_id', 36)->nullable(); // UUID
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Date d'expiration du code lui-même
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
            $table->foreign('used_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('used_by_company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('activated_license_id')->references('id')->on('licenses')->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['status', 'expires_at']);
            $table->index('batch_ref');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prepaid_vouchers');
    }
};
