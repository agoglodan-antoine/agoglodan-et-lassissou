<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * NB : le trigger `trg_actualites_auteur_role` interdit au rôle "acheteur"
 * de publier une actualité. Ce seeder choisit uniquement des auteurs
 * appartenant aux autres rôles.
 */
class ActualiteSeeder extends Seeder
{
    public function run(): void
    {
        $auteurs = DB::table('utilisateurs')->where('role', '!=', 'acheteur')
            ->inRandomOrder()->limit(4)->pluck('id_utilisateur')->values();

        $articles = [
            ['titre' => "Bonnes pratiques d'élevage bovin en saison sèche", 'contenu' => "Conseils pratiques pour maintenir la santé du troupeau pendant la saison sèche au Bénin."],
            ['titre' => 'Comment bien choisir sa provende', 'contenu' => "Guide pour sélectionner une provende adaptée aux besoins nutritionnels de vos animaux."],
            ['titre' => 'Prévenir les maladies aviaires courantes', 'contenu' => "Panorama des maladies fréquentes en aviculture et des mesures de prévention à adopter."],
            ['titre' => 'ElevConnect renforce la logistique de livraison', 'contenu' => "Présentation des nouvelles zones de couverture pour les livraisons partout au Bénin."],
        ];

        foreach ($auteurs as $i => $id) {
            DB::table('actualites')->insert([
                'id_auteur' => $id,
                'titre' => $articles[$i]['titre'],
                'contenu' => $articles[$i]['contenu'],
                'date_publication' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
