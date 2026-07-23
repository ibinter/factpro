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
        Schema::create('security_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            // Politique mot de passe
            $table->integer('password_min_length')->default(8);
            $table->boolean('password_require_uppercase')->default(false);
            $table->boolean('password_require_number')->default(false);
            $table->boolean('password_require_symbol')->default(false);
            $table->integer('password_expiry_days')->default(0);
            $table->integer('password_history_count')->default(0);
            // Sessions
            $table->integer('session_lifetime_minutes')->default(120);
            $table->boolean('single_session')->default(false);
            $table->integer('max_login_attempts')->default(5);
            $table->integer('lockout_minutes')->default(15);
            $table->boolean('require_2fa')->default(false);
            // IP
            $table->json('allowed_ips')->nullable();
            $table->boolean('log_all_access')->default(false);
            $table->timestamps();
            $table->unique('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_policies');
    }
};
