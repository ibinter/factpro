<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AcademyController extends Controller
{
    /**
     * Téléchargement d'une ressource de l'académie.
     *
     * Les fichiers réels ne sont pas encore disponibles.
     * On retourne un 404 avec un message explicatif.
     */
    public function download(string $slug)
    {
        abort(404, 'Ce fichier sera disponible prochainement. Contactez support@ibigsoft.com pour le recevoir maintenant.');
    }
}
