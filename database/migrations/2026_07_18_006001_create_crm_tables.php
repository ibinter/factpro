<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('prospect_name', 100)->nullable();
            $table->string('prospect_email', 255)->nullable();
            $table->string('prospect_phone', 30)->nullable();
            $table->enum('stage', ['prospect', 'contacted', 'qualified', 'quote_sent', 'won', 'lost'])->default('prospect');
            $table->decimal('value', 15, 2)->nullable();
            $table->integer('probability')->nullable();
            $table->string('source', 50)->nullable();
            $table->text('notes')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('deal_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['note', 'call', 'email', 'meeting', 'stage_change', 'document_created']);
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_activities');
        Schema::dropIfExists('deals');
    }
};
