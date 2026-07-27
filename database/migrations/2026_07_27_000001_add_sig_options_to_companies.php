<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('sig_show_emitter')->default(true)->after('show_stamp');
            $table->boolean('sig_show_client')->default(true)->after('sig_show_emitter');
            $table->string('sig_mode', 20)->default('manual')->after('sig_show_client'); // manual | digital | both
            $table->text('sig_custom_mention')->nullable()->after('sig_mode');
            $table->string('sig_emitter_label', 150)->nullable()->after('sig_custom_mention');
            $table->string('sig_client_label', 150)->nullable()->after('sig_emitter_label');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['sig_show_emitter', 'sig_show_client', 'sig_mode', 'sig_custom_mention', 'sig_emitter_label', 'sig_client_label']);
        });
    }
};
