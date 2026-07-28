<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_document_id')->nullable()->after('id');
            $table->string('conversion_note')->nullable()->after('parent_document_id');

            $table->foreign('parent_document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('set null');

            $table->index('parent_document_id');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['parent_document_id']);
            $table->dropIndex(['parent_document_id']);
            $table->dropColumn(['parent_document_id', 'conversion_note']);
        });
    }
};
