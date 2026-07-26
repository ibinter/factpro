<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activation_keys', function (Blueprint $table) {
            $table->id();
            $table->string('code', 24)->unique(); // IBFP-XXXX-XXXX-XXXX
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete(); // réservée à une société spécifique
            $table->string('batch', 50)->index(); // ex: LOT-2026-001
            $table->foreignId('used_by_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('duration_days'); // 30, 365, etc.
            $table->enum('status', ['available', 'used', 'expired', 'revoked'])->default('available')->index();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('revocation_reason')->nullable();
            $table->foreignId('generated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activation_keys');
    }
};
