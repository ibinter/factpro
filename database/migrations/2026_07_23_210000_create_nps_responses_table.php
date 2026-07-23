<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nps_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('score'); // 0-10
            $table->text('comment')->nullable();
            $table->string('context')->default('app'); // app, email, exit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nps_responses');
    }
};
