<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename legacy HR contracts table if it still uses 'contracts' name and new table doesn't exist yet
        if (Schema::hasColumn('contracts', 'employee_id')) {
            Schema::rename('contracts', 'employee_contracts');
            // Drop FKs that kept the old 'contracts_*' name so new contracts table can reuse the names
            try {
                \DB::statement('ALTER TABLE `employee_contracts` DROP FOREIGN KEY `contracts_company_id_foreign`');
            } catch (\Throwable) {}
            try {
                \DB::statement('ALTER TABLE `employee_contracts` DROP FOREIGN KEY `contracts_employee_id_foreign`');
            } catch (\Throwable) {}
        }

        // Skip creation if contracts already has the new schema
        if (Schema::hasColumn('contracts', 'customer_id')) {
            return;
        }

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('service'); // service, prestation, maintenance, nda, other
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->integer('alert_days_before')->default(30);
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('currency', 5)->default('XOF');
            $table->string('status')->default('draft'); // draft, active, expired, terminated
            $table->integer('current_version')->default(1);
            $table->text('notes')->nullable();
            $table->json('signatories')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
