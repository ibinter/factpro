<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // caissier

            $table->string('status', 10)->default('open'); // open | closed

            $table->decimal('opening_float', 15, 2)->default(0);   // fonds de caisse initial
            $table->decimal('expected_cash', 15, 2)->nullable();   // espèces attendues à la clôture
            $table->decimal('counted_cash', 15, 2)->nullable();    // espèces comptées à la clôture
            $table->decimal('difference', 15, 2)->nullable();      // écart de caisse

            $table->unsignedInteger('tickets_count')->default(0);
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->json('totals_by_method')->nullable();          // {cash: x, mobile_money: y, …}

            // MariaDB : dateTime (pas timestamp) pour éviter le piège des deux
            // timestamps NOT NULL sans défaut. opened_at est rempli explicitement.
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
