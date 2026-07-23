<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('roadmap_features', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('category')->default('general'); // general, pos, facturation, stocks, api, mobile
            $table->string('status')->default('planned'); // planned, in_progress, delivered, cancelled
            $table->integer('votes_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('roadmap_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained('roadmap_features')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['feature_id', 'user_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('roadmap_votes');
        Schema::dropIfExists('roadmap_features');
    }
};
