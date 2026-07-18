<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->default('CI');
            $table->string('currency', 3)->default('XOF');
            $table->string('tax_id')->nullable();          // N° contribuable / SIRET
            $table->string('trade_register')->nullable();  // RCCM
            $table->string('logo_path')->nullable();
            $table->text('invoice_footer')->nullable();
            $table->string('default_template', 40)->default('corporate-01');
            $table->decimal('default_tax_rate', 5, 2)->default(18.00); // TVA 18% zone OHADA
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('member'); // owner | admin | member | cashier
            $table->timestamps();
            $table->unique(['company_id', 'user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_company_id')->nullable()->after('remember_token')
                ->constrained('companies')->nullOnDelete();
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('country', 2)->default('CI')->after('phone');
            $table->string('locale', 5)->default('fr')->after('country');
            $table->boolean('is_superadmin')->default(false)->after('locale');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_company_id');
            $table->dropColumn(['phone', 'country', 'locale', 'is_superadmin']);
        });
        Schema::dropIfExists('company_user');
        Schema::dropIfExists('companies');
    }
};
