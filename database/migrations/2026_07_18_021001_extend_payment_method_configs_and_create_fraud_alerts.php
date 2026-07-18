<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extension de payment_method_configs avec les colonnes manquantes
        Schema::table('payment_method_configs', function (Blueprint $table) {
            $table->string('slug', 50)->nullable()->unique()->after('type');
            $table->string('account_name', 200)->nullable()->after('account_holder');
            $table->string('rib', 50)->nullable()->after('iban');
            $table->string('branch', 100)->nullable()->after('rib');
            $table->string('routing_number', 50)->nullable()->after('branch');
            $table->text('address')->nullable()->after('routing_number');
            $table->string('processing_time', 50)->nullable()->after('instructions');
            $table->json('allowed_plan_ids')->nullable()->after('metadata');
            $table->unsignedSmallInteger('display_order')->default(0)->after('sort_order');
            $table->softDeletes();
        });

        // Table des alertes fraude
        Schema::create('fraud_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('alert_type', 100);
            // amount_mismatch | duplicate_proof_hash | duplicate_reference | suspicious_new_account
            // | multiple_pending | repeated_provisional | country_mismatch | expired_transaction
            // | illegible_proof | name_mismatch
            $table->unsignedSmallInteger('score')->default(1);
            $table->text('description')->nullable();
            $table->json('flags')->nullable();
            $table->string('status', 30)->default('open');
            // open | reviewed | dismissed | confirmed_fraud
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_alerts');

        Schema::table('payment_method_configs', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'slug', 'account_name', 'rib', 'branch', 'routing_number',
                'address', 'processing_time', 'allowed_plan_ids', 'display_order',
            ]);
        });
    }
};
