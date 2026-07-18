<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sessions', function (Blueprint $table) {
            // Fond de caisse restitué à la clôture
            $table->decimal('closing_float', 15, 2)->nullable()->after('opening_float');

            // Rapport Z — horodatage et numéro séquentiel
            $table->timestamp('z_report_generated_at')->nullable()->after('closed_at');
            $table->string('z_report_number', 20)->nullable()->after('z_report_generated_at');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sessions', function (Blueprint $table) {
            $table->dropColumn(['closing_float', 'z_report_generated_at', 'z_report_number']);
        });
    }
};
