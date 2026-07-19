<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_lines', function (Blueprint $table) {
            $table->string('line_discount_type', 10)->default('percent')->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('document_lines', function (Blueprint $table) {
            $table->dropColumn('line_discount_type');
        });
    }
};
