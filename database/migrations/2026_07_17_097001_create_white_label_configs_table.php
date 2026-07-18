<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('white_label_configs', function (Blueprint $table) {
            $table->id();
            $table->string('subdomain', 63)->unique()->nullable();
            $table->string('app_name', 100)->default('IBIG FactPro');
            $table->string('logo_url', 500)->nullable();
            $table->string('primary_color', 7)->default('#0062CC');
            $table->string('secondary_color', 7)->default('#002D5B');
            $table->string('accent_color', 7)->default('#F0C040');
            $table->text('footer_text')->nullable();
            $table->string('support_email', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('white_label_configs');
    }
};
