<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table des taux de change (cahier IBIG §3 DEV / §14 « Multi-devises »).
 * Rafraîchie depuis une API publique (fallback taux fixes dérivés du pivot
 * réglementaire 1 EUR = 655,957 XOF). Une ligne par couple base/devise.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3);
            $table->string('currency', 3);
            $table->decimal('rate', 18, 8);          // 1 base = rate * currency
            $table->dateTime('fetched_at');
            $table->string('source', 30)->default('api'); // api | fallback
            $table->timestamps();

            $table->unique(['base_currency', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
