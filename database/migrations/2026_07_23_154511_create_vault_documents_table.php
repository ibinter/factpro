<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vault_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('document_type'); // invoice, contract, payslip, ged
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_type')->nullable(); // morphable
            $table->string('title');
            $table->string('file_path'); // storage/vault/{company_id}/{year}/{hash}.pdf
            $table->string('file_hash'); // SHA-256 du fichier
            $table->string('archive_hash'); // SHA-256(file_hash + metadata + timestamp)
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type')->default('application/pdf');
            $table->timestamp('archived_at');
            $table->string('retention_until'); // date ISO jusqu'à laquelle le doc doit être conservé
            $table->integer('retention_years')->default(10);
            $table->boolean('is_sealed')->default(true); // immuable une fois scellé
            $table->json('metadata')->nullable(); // {document_number, amount, parties, ...}
            $table->string('seal_certificate')->nullable(); // hash de l'ensemble pour intégrité
            $table->timestamps();
            $table->index(['company_id', 'document_type']);
            $table->index('archive_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_documents');
    }
};
