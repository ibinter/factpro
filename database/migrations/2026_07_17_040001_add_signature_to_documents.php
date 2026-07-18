<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Signature électronique des devis (cahier IBIG §22.1 / §3 CTR).
 * Champs post-scellement : NE FONT PAS partie du canonicalPayload() du hash
 * d'intégrité — la signature intervient après l'émission du document.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('signature_path')->nullable()->after('finalized_at');
            $table->string('signed_by_name')->nullable()->after('signature_path');
            $table->dateTime('signed_at')->nullable()->after('signed_by_name');
            $table->string('signature_ip', 45)->nullable()->after('signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['signature_path', 'signed_by_name', 'signed_at', 'signature_ip']);
        });
    }
};
