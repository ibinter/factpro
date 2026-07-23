<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('template_color_primary', 7)->nullable()->after('template_key');
            $table->string('template_color_secondary', 7)->nullable()->after('template_color_primary');
            $table->string('template_color_accent', 7)->nullable()->after('template_color_secondary');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['template_color_primary', 'template_color_secondary', 'template_color_accent']);
        });
    }
};
