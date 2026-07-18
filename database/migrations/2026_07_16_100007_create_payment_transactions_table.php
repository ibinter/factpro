<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('payment_provider', 40); // moneroo | orange_money | mtn_momo | wave | moov | bank_transfer_national | bank_transfer_international | cash
            $table->string('provider_reference')->nullable();  // référence externe (Moneroo, opérateur…)
            $table->string('internal_reference', 40)->unique(); // FP-{YYYYMMDD}-{RANDOM6}
            $table->decimal('amount_expected', 15, 2);
            $table->decimal('amount_declared', 15, 2)->nullable();
            $table->decimal('amount_received', 15, 2)->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('initiated');
            // initiated | pending | processing | succeeded | failed | cancelled | expired
            // | under_review | manually_validated | rejected | refunded | disputed
            $table->string('sender_name')->nullable();   // paiements manuels
            $table->string('sender_number', 40)->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->json('metadata')->nullable(); // réponse brute fournisseur
            $table->text('notes')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transaction_id')->constrained('payment_transactions')->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path'); // stockage privé — jamais public
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('file_hash', 64)->nullable(); // SHA-256 anti-doublon
            $table->foreignId('uploaded_by')->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('verification_status', 30)->default('pending');
            // pending | approved | rejected | complement_requested
            $table->text('internal_comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
        Schema::dropIfExists('payment_transactions');
    }
};
