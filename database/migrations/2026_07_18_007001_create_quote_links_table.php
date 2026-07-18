<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->string('password', 255)->nullable();
            $table->boolean('allow_comments')->default(true);
            $table->boolean('allow_decline')->default(true);
            $table->boolean('require_signature')->default(true);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->text('decline_reason')->nullable();
            $table->text('client_comment')->nullable();
            $table->string('client_name', 100)->nullable();
            $table->string('client_email', 255)->nullable();
            $table->string('client_ip', 45)->nullable();
            $table->text('client_signature_data')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_links');
    }
};
