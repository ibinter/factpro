<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('country', 2);
            $table->string('tax_regime', 30)->default('custom');
            $table->json('tva_rates');
            $table->boolean('has_tps')->default(false);
            $table->decimal('tps_rate', 5, 2)->default(1.00);
            $table->boolean('has_oca')->default(false);
            $table->decimal('oca_rate', 5, 2)->default(0.50);
            $table->boolean('has_timbre')->default(false);
            $table->decimal('timbre_amount', 10, 2)->default(0);
            $table->string('declaration_frequency', 10)->default('monthly');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_configs');
    }
};
