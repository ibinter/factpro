<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->enum('source', ['zapier', 'make', 'custom'])->default('custom');
            $table->string('secret_token', 64)->unique();
            $table->json('allowed_actions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_called_at')->nullable();
            $table->unsignedInteger('calls_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_webhooks');
    }
};
