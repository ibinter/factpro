<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('level'); // 1 courtois | 2 ferme | 3 mise en demeure
            $table->string('channel', 15)->default('email');
            $table->string('sent_to');
            $table->string('subject');
            $table->string('triggered_by', 15); // auto | manual
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('sent_at');
            $table->timestamps();

            $table->index(['document_id', 'level']);
            $table->index(['company_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
