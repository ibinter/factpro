<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 20);      // USDT, BTC, ETH, BNB, USDC
            $table->string('network', 30);       // TRC20, ERC20, BEP20, Bitcoin, Ethereum
            $table->string('wallet_address', 200);
            $table->string('label', 100)->nullable(); // "USDT TRC20 (Tron)"
            $table->string('qr_code_url', 255)->nullable(); // URL image QR du wallet
            $table->text('instructions')->nullable();
            $table->integer('confirmations_required')->default(1);
            $table->boolean('is_active')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_wallets');
    }
};
