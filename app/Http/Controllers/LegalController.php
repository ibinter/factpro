<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    private array $pages = [
        'mentions'                 => 'Mentions légales',
        'cgu'                      => "Conditions générales d'utilisation",
        'conditions-commerciales'  => 'Conditions commerciales',
        'contrat-licence'          => 'Contrat de licence utilisateur',
        'confidentialite'          => 'Politique de confidentialité',
        'cookies'                  => 'Politique de cookies',
        'sauvegarde'               => 'Politique de sauvegarde',
        'support'                  => 'Politique de support',
        'resiliation'              => 'Politique de résiliation',
        'remboursement'            => 'Politique de remboursement',
        'traitement-donnees'       => 'Traitement des données personnelles',
        'pi'                       => 'Propriété intellectuelle',
        'propriete-marque'         => 'Protection de la marque',
        'essai'                    => "Conditions du programme d'essai",
        'sara'                     => "Conditions d'utilisation de SARA",
        'ia'                       => "Limitation de responsabilité de l'IA",
        'suppression-compte'       => 'Gestion et suppression du compte',
        'reclamations'             => 'Gestion des réclamations',
        // Slugs additionnels (routes public.php)
        'sla'                      => 'Accord de niveau de service (SLA)',
        'securite'                 => 'Politique de sécurité',
        'accessibilite'            => "Politique d'accessibilité",
        'anti-spam'                => 'Politique anti-spam',
        'conditions-api'           => "Conditions d'utilisation de l'API",
        'partenaires'              => 'Politique partenaires',
        'utilisation-acceptable'   => "Politique d'utilisation acceptable",
        'rgpd-details'             => 'Détails RGPD',
        'dpa'                      => 'Accord de traitement des données (DPA)',
        'plan-continuite'          => "Plan de continuité d'activité",
        'charte-ethique'           => 'Charte éthique IBIG SOFT',
    ];

    public function show(string $slug): Response
    {
        if (! array_key_exists($slug, $this->pages)) {
            abort(404);
        }

        return Inertia::render('Public/Legal', [
            'page' => $this->pages[$slug],
            'slug' => $slug,
            'year' => (int) date('Y'),
        ]);
    }
}
