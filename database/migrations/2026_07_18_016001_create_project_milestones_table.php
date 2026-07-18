<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 15 — Jalons de projet (project_milestones).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->tinyInteger('completion_pct')->default(0); // 0-100
            $table->enum('status', ['pending', 'in_progress', 'completed', 'invoiced'])->default('pending');
            $table->decimal('billing_amount', 12, 2)->nullable();
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};
