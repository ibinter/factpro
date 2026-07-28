<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('wa_enabled')->default(false)->after('payment_methods');
            $table->string('wa_phone_number_id')->nullable()->after('wa_enabled');
            $table->text('wa_access_token')->nullable()->after('wa_phone_number_id');
            $table->string('wa_business_account_id')->nullable()->after('wa_access_token');
            $table->string('wa_verify_token')->nullable()->after('wa_business_account_id');
        });

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('to_phone');
            $table->enum('message_type', ['text', 'template'])->default('text');
            $table->string('template_name')->nullable();
            $table->text('body');
            $table->enum('status', ['queued', 'sent', 'delivered', 'read', 'failed'])->default('queued')->index();
            $table->string('wa_message_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'wa_enabled',
                'wa_phone_number_id',
                'wa_access_token',
                'wa_business_account_id',
                'wa_verify_token',
            ]);
        });
    }
};
