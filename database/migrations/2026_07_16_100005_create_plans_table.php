<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // starter | pro | business | enterprise
            $table->string('name');
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 15, 2);          // en devise de référence
            $table->decimal('promo_price', 15, 2)->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->unsignedTinyInteger('trial_days')->default(7);
            $table->json('features');   // liste des fonctionnalités incluses
            $table->json('limits');     // documents_per_month, users, companies, customers, products, templates, storage_mb
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
