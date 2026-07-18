<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->tinyInteger('seats')->default(4);
            $table->enum('status', ['free', 'occupied', 'reserved'])->default('free');
            $table->foreignId('current_pos_session_id')->nullable()->constrained('pos_sessions')->nullOnDelete();
            $table->json('order_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_tables');
    }
};
