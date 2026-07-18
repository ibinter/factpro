<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('recipient_email');
            $table->string('tracking_token', 64)->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->unsignedInteger('opens_count')->default(0);
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->unsignedInteger('clicks_count')->default(0);
            $table->timestamp('last_clicked_at')->nullable();
            $table->string('client_ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('alert_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_tracking');
    }
};
