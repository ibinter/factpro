<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignUuid('transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->string('license_key', 30)->unique(); // FP-XXXX-XXXX-XXXX-XXXX
            $table->string('type', 20)->default('trial'); // trial | paid | provisional | legacy
            $table->string('status', 20)->default('trial');
            // trial | pending | provisional | active | grace_period | suspended | expired | terminated | revoked
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('grace_period_ends_at')->nullable();
            $table->dateTime('trial_ends_at')->nullable();
            $table->json('limits')->nullable();          // copie des limites du plan à l'activation
            $table->string('activation_source', 30)->default('trial'); // payment | manual | provisional | api | trial | legacy
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('webhook_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider', 40);
            $table->string('event_type', 60)->nullable();
            $table->string('event_id')->nullable(); // identifiant unique fournisseur
            $table->json('payload');
            $table->text('signature_header')->nullable();
            $table->boolean('signature_valid')->default(false);
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignUuid('transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamps();

            $table->unique(['provider', 'event_id']);
        });

        Schema::create('payment_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60); // order_created, payment_launched, proof_submitted, license_activated…
            $table->string('entity_type', 30); // order | transaction | license | plan | config | proof
            $table->string('entity_id', 40);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('reason')->nullable(); // obligatoire pour actions sensibles
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('payment_method_configs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40); // mobile_money | bank_national | bank_international | transfer_service
            $table->string('country', 2)->nullable();
            $table->string('label');           // Orange Money CI, Banque Atlantique…
            $table->string('operator', 60)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('iban', 40)->nullable();
            $table->string('swift_bic', 15)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->text('instructions')->nullable();
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_method_configs');
        Schema::dropIfExists('payment_audit_logs');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('licenses');
    }
};
