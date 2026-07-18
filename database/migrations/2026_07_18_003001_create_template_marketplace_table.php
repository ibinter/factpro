<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_marketplace', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('base_template', 50);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('primary_color', 7);
            $table->string('secondary_color', 7);
            $table->string('accent_color', 7);
            $table->text('custom_css')->nullable();
            $table->json('preview_data')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->integer('downloads_count')->default(0);
            $table->integer('rating_sum')->default(0);
            $table->integer('rating_count')->default(0);
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_marketplace');
    }
};
