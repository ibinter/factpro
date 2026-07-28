<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $features = [
            [
                'title'        => 'Catalogue de 498 modèles de documents sectoriels',
                'description'  => '498 modèles organisés en 24 catégories avec aperçu interactif et sélection automatique du style PDF.',
                'category'     => 'facturation',
                'status'       => 'delivered',
                'sort_order'   => 9,
                'votes_count'  => 0,
                'delivered_at' => '2026-07-01',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'title'        => 'Templates PDF intelligents par type de document',
                'description'  => 'Recommandation automatique du style visuel selon le type (transport, juridique, BTP, médical, etc.).',
                'category'     => 'facturation',
                'status'       => 'delivered',
                'sort_order'   => 10,
                'votes_count'  => 0,
                'delivered_at' => '2026-07-01',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ];

        foreach ($features as $feature) {
            $exists = DB::table('roadmap_features')
                ->where('title', $feature['title'])
                ->exists();

            if (! $exists) {
                DB::table('roadmap_features')->insert($feature);
            }
        }
    }

    public function down(): void
    {
        DB::table('roadmap_features')->whereIn('title', [
            'Catalogue de 498 modèles de documents sectoriels',
            'Templates PDF intelligents par type de document',
        ])->delete();
    }
};
