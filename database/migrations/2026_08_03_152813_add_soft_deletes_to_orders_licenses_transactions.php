<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['orders', 'licenses', 'payment_transactions'] as $tbl) {
            if (Schema::hasTable($tbl) && ! Schema::hasColumn($tbl, 'deleted_at')) {
                Schema::table($tbl, fn (Blueprint $t) => $t->softDeletes());
            }
        }
    }

    public function down(): void
    {
        foreach (['orders', 'licenses', 'payment_transactions'] as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'deleted_at')) {
                Schema::table($tbl, fn (Blueprint $t) => $t->dropSoftDeletes());
            }
        }
    }
};
