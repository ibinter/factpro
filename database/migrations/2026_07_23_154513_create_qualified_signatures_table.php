<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('qualified_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->morphs('signable');
            $table->string('signer_name');
            $table->string('signer_email');
            $table->string('signer_role')->nullable();
            $table->string('status')->default('pending');
            $table->string('signature_level')->default('advanced');
            $table->string('token', 64)->unique();
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('expires_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->text('signature_data')->nullable();
            $table->string('certificate_hash')->nullable();
            $table->json('audit_trail')->nullable();
            $table->string('otp_code', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->integer('otp_attempts')->default(0);
            $table->string('document_hash')->nullable();
            $table->string('signed_file_path')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qualified_signatures');
    }
};
