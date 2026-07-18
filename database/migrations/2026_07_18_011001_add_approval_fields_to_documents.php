<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('approval_status', ['none', 'pending_approval', 'approved', 'rejected'])
                ->default('none')
                ->after('status');
            $table->foreignId('approval_workflow_id')
                ->nullable()
                ->constrained('approval_workflows')
                ->nullOnDelete()
                ->after('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['approval_workflow_id']);
            $table->dropColumn(['approval_status', 'approval_workflow_id']);
        });
    }
};
