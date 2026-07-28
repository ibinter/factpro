<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repairs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('ticket_number', 30);
            $table->string('device_type'); // smartphone/tablet/computer/printer/tv/other
            $table->string('brand')->nullable();
            $table->string('model_name')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('issue_description');
            $table->text('diagnosis')->nullable();
            $table->enum('status', ['received', 'diagnosing', 'waiting_parts', 'repairing', 'ready', 'delivered', 'cancelled'])->default('received');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->string('technician_name')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('final_cost', 10, 2)->nullable();
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('promised_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('customer_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'ticket_number']);
        });

        Schema::create('repair_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_id')->constrained('repairs')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 8, 2);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('line_total', 10, 2)->storedAs('quantity * unit_cost');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_parts');
        Schema::dropIfExists('repairs');
    }
};
