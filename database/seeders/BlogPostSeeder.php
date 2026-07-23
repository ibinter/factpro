<?php
namespace Database\Seeders;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder {
    public function run(): void {
        $posts = [
            [
                'title' => 'Comment créer votre première facture avec IBIG FactPro',
                'excerpt' => 'Découvrez comment générer une facture professionnelle en moins de 2 minutes avec notre logiciel de facturation.',
                'content' => "IBIG FactPro rend la facturation simple et rapide pour toutes les PME africaines.\n\n## Étape 1 : Accéder au module Documents\n\nDepuis votre tableau de bord, cliquez sur « Documents » dans le menu latéral, puis sur « Nouvelle facture ».\n\n## Étape 2 : Sélectionner un client\n\nChoisissez un client existant dans votre base ou créez-en un nouveau en quelques clics.\n\n## Étape 3 : Ajouter vos produits ou services\n\nAjoutez les lignes de votre facture avec la quantité, le prix unitaire et la TVA applicable.\n\n## Étape 4 : Envoyer la facture\n\nGénérez le PDF et envoyez-le directement par email à votre client. C'est aussi simple que ça !\n\nFactPro gère automatiquement la numérotation, les calculs de TVA conformes à la réglementation OHADA, et conserve un historique complet de vos factures.",
                'category' => 'tutoriels',
                'author_name' => 'Équipe IBIG',
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'meta_title' => 'Créer une facture avec FactPro — Guide pas à pas',
                'meta_description' => 'Apprenez à créer votre première facture professionnelle avec IBIG FactPro en moins de 2 minutes.',
            ],
            [
                'title' => 'IBIG FactPro désormais disponible dans 18 pays d\'Afrique',
                'excerpt' => 'Nous sommes fiers d\'annoncer l\'expansion de FactPro à 18 nouveaux pays africains avec un support multi-devises et multi-fiscalités.',
                'content' => "Après des mois de développement intensif, IBIG FactPro est désormais disponible dans 18 pays d'Afrique francophone et anglophone.\n\n## Les nouveaux pays couverts\n\nCôte d'Ivoire, Sénégal, Mali, Burkina Faso, Niger, Guinée, Togo, Bénin, Cameroun, Gabon, Congo, RDC, Madagascar, Mauritanie, Maroc, Algérie, Tunisie, et Côte d'Ivoire.\n\n## Conformité fiscale locale\n\nChaque pays bénéficie d'une configuration fiscale adaptée : taux de TVA locaux, formats de numérotation conformes, devises locales (XOF, XAF, MAD, DZD, TND).\n\n## Support multilingue\n\nL'interface est disponible en français, anglais et arabe pour répondre aux besoins de toutes les entreprises de la région.\n\nContactez notre équipe pour découvrir comment FactPro peut transformer votre gestion commerciale.",
                'category' => 'entreprise',
                'author_name' => 'Direction IBIG Soft',
                'status' => 'published',
                'published_at' => now()->subDays(12),
                'meta_title' => 'FactPro disponible dans 18 pays africains',
                'meta_description' => 'IBIG FactPro s\'étend à 18 pays d\'Afrique avec support multi-devises, multi-fiscalités et interface multilingue.',
            ],
            [
                'title' => '5 raisons pour lesquelles les PME africaines choisissent FactPro',
                'excerpt' => 'Découvrez pourquoi plus de 500 entreprises font confiance à IBIG FactPro pour leur gestion commerciale quotidienne.',
                'content' => "Les PME africaines font face à des défis uniques : conformité OHADA, gestion multi-devises, équipes terrain sans connexion permanente. FactPro a été conçu pour ces réalités.\n\n## 1. Conformité OHADA native\n\nFactPro respecte le droit OHADA et les réglementations fiscales locales. Vos factures sont juridiquement valides dans tous les pays membres.\n\n## 2. Fonctionne hors connexion\n\nGrâce à notre mode PWA, votre équipe peut continuer à créer des devis et des bons de livraison même sans internet. La synchronisation se fait automatiquement dès le retour de la connexion.\n\n## 3. Caisse POS intégrée\n\nLe module POS tactile transforme n'importe quelle tablette en caisse enregistreuse. Parfait pour les points de vente physiques.\n\n## 4. Prix adapté au marché africain\n\nAvec un plan Starter à 4 900 FCFA/mois, FactPro est accessible à toutes les PME, sans frais cachés ni engagement minimum.\n\n## 5. Support en français 24/7\n\nNotre équipe basée à Abidjan vous accompagne dans votre langue, avec une connaissance approfondie du contexte business africain.\n\nEssayez gratuitement pendant 14 jours — sans carte bancaire.",
                'category' => 'produit',
                'author_name' => 'Équipe IBIG',
                'status' => 'published',
                'published_at' => now()->subDays(20),
                'meta_title' => '5 raisons de choisir FactPro pour votre PME africaine',
                'meta_description' => 'Conformité OHADA, mode hors-ligne, POS intégré, prix accessible : découvrez pourquoi 500+ PME font confiance à FactPro.',
            ],
        ];
        foreach ($posts as $post) {
            BlogPost::firstOrCreate(['title' => $post['title']], $post + ['slug' => \Illuminate\Support\Str::slug($post['title']).'-'.uniqid()]);
        }
    }
}
