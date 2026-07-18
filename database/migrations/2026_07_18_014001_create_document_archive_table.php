<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->unique()->constrained('documents')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->timestamp('archived_at');
            $table->string('document_hash', 64);
            $table->string('pdf_path', 500);
            $table->string('signature', 700);
            $table->string('public_key_fingerprint', 64);
            $table->tinyInteger('archive_version')->default(1);
            $table->integer('pdf_size')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_archives');
    }
};
