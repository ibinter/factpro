<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('position', 100);
            $table->string('department', 100)->nullable();
            $table->date('hire_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'suspended', 'terminated'])->default('active');
            $table->string('cnss_number', 50)->nullable();
            $table->string('social_security_regime', 20)->default('cnss_ci');
            $table->string('bank_iban', 50)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->json('emergency_contact')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['cdi', 'cdd', 'stage', 'freelance'])->default('cdi');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('gross_salary', 12, 2);
            $table->string('currency', 3)->default('XOF');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('period_month');
            $table->smallInteger('period_year');
            $table->decimal('gross_salary', 12, 2);
            $table->json('employee_contributions');
            $table->json('employer_contributions');
            $table->decimal('net_salary', 12, 2);
            $table->decimal('total_employer_cost', 12, 2);
            $table->string('currency', 3)->default('XOF');
            $table->enum('status', ['draft', 'validated', 'paid'])->default('draft');
            $table->date('payment_date')->nullable();
            $table->string('pdf_path', 500)->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'period_month', 'period_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('employee_contracts');
        Schema::dropIfExists('employees');
    }
};
