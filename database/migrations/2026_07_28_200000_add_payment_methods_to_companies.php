<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Duplicate of 2026_07_28_000001 — column already added; kept for migration history integrity
        if (!Schema::hasColumn('companies', 'payment_methods')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->json('payment_methods')->nullable()->after('invoice_footer');
            });
        }
    }

    public function down(): void
    {
        // No-op: the paired migration 2026_07_28_000001 handles the rollback
    }
};
