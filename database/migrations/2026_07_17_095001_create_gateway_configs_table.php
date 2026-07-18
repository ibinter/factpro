<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_configs', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 30)->unique(); // cinetpay | fedapay | flutterwave | moneroo
            $table->boolean('is_active')->default(false);
            $table->text('config')->nullable();           // JSON encrypted : api_key, site_id, secret_key…
            $table->json('supported_countries')->nullable(); // codes ISO ex: ["CI","SN","BF"]
            $table->json('supported_currencies')->nullable(); // ex: ["XOF","XAF"]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_configs');
    }
};
