<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_features', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('category')->default('module'); // 'module' | 'feature' | 'integration'
            $table->string('name_fr');
            $table->string('name_en')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->string('icon')->nullable();
            $table->json('available_in_plans')->nullable(); // null = tous les plans
            $table->string('status')->default('available'); // available | coming_soon | beta | removed
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_features');
    }
};
