<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute les colonnes de parrainage à la table users.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 12)->unique()->nullable()->after('remember_token');
            $table->unsignedBigInteger('referred_by_id')->nullable()->after('referral_code');

            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('referred_by_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['referred_by_id']);
            }
            $table->dropColumn(['referral_code', 'referred_by_id']);
        });
    }
};
