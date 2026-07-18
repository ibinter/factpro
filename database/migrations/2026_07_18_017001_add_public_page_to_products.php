<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Slug public pour les companies (URL boutique)
        Schema::table('companies', function (Blueprint $table) {
            $table->string('slug', 100)->nullable()->unique()->after('name');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('public_page_enabled')->default(false)->after('is_active');
            $table->string('public_slug', 100)->nullable()->unique()->after('public_page_enabled');
            $table->text('public_description')->nullable()->after('public_slug');
            $table->json('public_images')->nullable()->after('public_description');
            $table->boolean('allow_online_order')->default(false)->after('public_images');
            $table->integer('minimum_order_qty')->default(1)->after('allow_online_order');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'public_page_enabled',
                'public_slug',
                'public_description',
                'public_images',
                'allow_online_order',
                'minimum_order_qty',
            ]);
        });
    }
};
