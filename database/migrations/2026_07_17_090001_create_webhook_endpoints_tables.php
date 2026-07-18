<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('url');
            $table->string('secret');
            $table->json('events');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            }
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('webhook_endpoint_id');
            $table->string('event');
            $table->json('payload');
            $table->integer('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->tinyInteger('attempt')->default(1);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->foreign('webhook_endpoint_id')->references('id')->on('webhook_endpoints')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_endpoints');
    }
};
