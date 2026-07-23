<?php
namespace Database\Seeders;
use App\Models\RoadmapFeature;
use Illuminate\Database\Seeder;

class RoadmapSeeder extends Seeder {
    public function run(): void {
        $features = [
            ['title'=>'Application mobile native iOS & Android','description'=>'Application mobile dédiée avec mode hors-ligne complet, notifications push et scan de codes-barres.','category'=>'mobile','status'=>'in_progress','sort_order'=>1,'votes_count'=>47],
            ['title'=>'Intégration WhatsApp Business API','description'=>'Envoi automatique des factures, devis et relances directement sur WhatsApp avec accusé de lecture.','category'=>'general','status'=>'planned','sort_order'=>2,'votes_count'=>38],
            ['title'=>'Module comptabilité avancée (Plan comptable OHADA)','description'=>'Journaux comptables complets, bilan automatique, compte de résultat conforme au plan comptable OHADA révisé.','category'=>'facturation','status'=>'planned','sort_order'=>3,'votes_count'=>31],
            ['title'=>'Marketplace de plugins et extensions','description'=>'Écosystème ouvert permettant aux développeurs tiers de créer et distribuer des extensions pour FactPro.','category'=>'api','status'=>'planned','sort_order'=>4,'votes_count'=>24],
            ['title'=>'POS avec gestion de tables (restauration)','description'=>'Module caisse spécialisé restauration avec plan de salle interactif, commandes par table et tickets cuisine.','category'=>'pos','status'=>'delivered','sort_order'=>5,'votes_count'=>19,'delivered_at'=>now()->subDays(30)],
            ['title'=>'Import automatique relevés bancaires','description'=>'Réconciliation bancaire automatique par import OFX/QIF/CSV depuis toutes les banques africaines majeures.','category'=>'facturation','status'=>'planned','sort_order'=>6,'votes_count'=>16],
            ['title'=>'Gestion multi-entrepôts','description'=>'Suivi des stocks par entrepôt, transferts inter-sites, inventaire par emplacement.','category'=>'stocks','status'=>'planned','sort_order'=>7,'votes_count'=>14],
            ['title'=>'API webhooks entrants (Zapier/Make)','description'=>'Déclencheurs entrants permettant de créer des factures et clients depuis des outils tiers automatiquement.','category'=>'api','status'=>'delivered','sort_order'=>8,'votes_count'=>12,'delivered_at'=>now()->subDays(60)],
        ];
        foreach ($features as $f) {
            RoadmapFeature::firstOrCreate(['title' => $f['title']], $f);
        }
    }
}
