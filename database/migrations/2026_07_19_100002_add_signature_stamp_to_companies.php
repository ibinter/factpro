<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('signature_path')->nullable()->after('logo_path');
            $table->string('stamp_path')->nullable()->after('signature_path');
            $table->string('signature_label', 100)->nullable()->after('stamp_path');
            $table->boolean('show_signature')->default(false)->after('signature_label');
            $table->boolean('show_stamp')->default(false)->after('show_signature');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['signature_path', 'stamp_path', 'signature_label', 'show_signature', 'show_stamp']);
        });
    }
};
