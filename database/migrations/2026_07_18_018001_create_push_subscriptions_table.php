<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->text('endpoint');
            $table->string('public_key', 255);
            $table->string('auth_token', 255);
            $table->text('user_agent')->nullable();
            $table->timestamp('subscribed_at')->useCurrent();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Un seul enregistrement par (user, endpoint)
            $table->unique(['user_id', 'endpoint'], 'push_subscriptions_user_endpoint_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
