<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable(); // si prospect sans fiche
            $table->string('visit_type')->default('commercial'); // commercial, livraison, sav, prospection
            $table->string('status')->default('planned'); // planned, in_progress, completed, cancelled
            $table->decimal('lat_start', 10, 7)->nullable();
            $table->decimal('lng_start', 10, 7)->nullable();
            $table->decimal('lat_end', 10, 7)->nullable();
            $table->decimal('lng_end', 10, 7)->nullable();
            $table->string('address_visited')->nullable();
            $table->timestamp('planned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('objective')->nullable();
            $table->text('report')->nullable();  // compte-rendu visite
            $table->string('outcome')->nullable(); // positif, neutre, negatif, relance
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete(); // doc créé lors de la visite
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('customer_visits'); }
};
