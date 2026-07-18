<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_agents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('zone', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 10)->default('CI');
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('delivery_agent_id')->nullable()->constrained('delivery_agents')->nullOnDelete();
            $table->string('delivery_address', 300);
            $table->string('delivery_city', 100);
            $table->string('delivery_country', 10)->default('CI');
            $table->string('contact_phone', 30);
            $table->string('contact_name', 150);
            $table->text('delivery_notes')->nullable();
            $table->enum('status', [
                'pending', 'assigned', 'out_for_delivery',
                'delivered', 'payment_received', 'failed', 'returned',
            ])->default('pending');
            $table->decimal('cod_amount', 15, 2);
            $table->string('cod_currency', 10)->default('XOF');
            $table->decimal('amount_received', 15, 2)->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('payment_confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('confirmation_code', 10)->nullable();
            $table->text('agent_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
        Schema::dropIfExists('delivery_agents');
    }
};
