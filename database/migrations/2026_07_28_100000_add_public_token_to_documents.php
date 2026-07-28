<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable()->unique()->after('uuid');
        });

        // Renseigne le token sur les documents existants
        \App\Models\Document::withTrashed()->whereNull('public_token')->chunkById(200, function ($docs) {
            foreach ($docs as $doc) {
                $doc->updateQuietly(['public_token' => Str::random(48)]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }
};
