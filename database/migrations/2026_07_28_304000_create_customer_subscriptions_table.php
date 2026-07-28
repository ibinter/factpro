<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->enum('frequency', ['weekly', 'monthly', 'quarterly', 'biannual', 'annual'])->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('XOF');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_billing_date');
            $table->enum('status', ['active', 'paused', 'cancelled', 'expired'])->default('active')->index();
            $table->boolean('auto_generate_invoice')->default(true);
            $table->integer('payment_terms')->default(30);
            $table->text('notes')->nullable();
            $table->timestamp('last_billed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('customer_subscriptions')->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();
            $table->date('billing_date');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['generated', 'failed', 'skipped'])->default('generated');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('customer_subscriptions');
    }
};
