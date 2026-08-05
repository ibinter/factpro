<?php

use App\Http\Controllers\DocumentConversionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'license'])->group(function () {
    // NB : la route POST documents.convert est définie dans web.php (DocumentController@convert,
    // via DocumentService — chemin testé et corrigé). Ne PAS la redéclarer ici : ce fichier étant
    // requis après web.php, un doublon écraserait le bon contrôleur (DocumentConversionController@convert
    // appelait Document::generateNumber() inexistant → 500 sur toute conversion en production).

    Route::get('/documents/{document}/chain', [DocumentConversionController::class, 'chain'])
        ->name('documents.chain');
});
