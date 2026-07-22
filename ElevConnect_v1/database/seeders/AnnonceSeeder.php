<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * NB : le trigger `trg_annonces_type_role` impose que
 * type_annonce=animal -> auteur eleveur, provende -> vendeur_provende,
 * accessoire -> vendeur_accessoire. Ce seeder respecte cette règle.
 */
class AnnonceSeeder extends Seeder
{
    public function run(): void
    {
        $eleveurs = DB::table('eleveurs')->pluck('id_utilisateur')->values();
        $vendeursProvende = DB::table('vendeur_provende')->pluck('id_utilisateur')->values();
        $vendeursAccessoire = DB::table('vendeur_accessoire')->pluck('id_utilisateur')->values();

        $annonces = [
            [
                'id_utilisateur' => $eleveurs[0],
                'type_annonce' => 'animal',
                'titre' => 'Zébu Borgou 18 mois',
                'description' => "Zébu en bonne santé, vacciné, prêt pour la vente.",
                'prix_unitaire' => 250000,
                'quantite' => 3,
                'poids' => 320.50,
                'mois' => 18,
                'unite_de_mesure' => null,
                'image_1' => 'annonces/zebu1.jpg',
                'image_2' => 'annonces/zebu1b.jpg',
                'statut' => 'visible',
            ],
            [
                'id_utilisateur' => $eleveurs[1],
                'type_annonce' => 'animal',
                'titre' => 'Poulets de chair prêts à vendre',
                'description' => "Lot de poulets élevés en plein air, alimentation naturelle.",
                'prix_unitaire' => 4500,
                'quantite' => 50,
                'poids' => 2.20,
                'mois' => 3,
                'unite_de_mesure' => null,
                'image_1' => 'annonces/poulets1.jpg',
                'image_2' => null,
                'statut' => 'visible',
            ],
            [
                'id_utilisateur' => $vendeursProvende[0],
                'type_annonce' => 'provende',
                'titre' => 'Sac de provende ponte 50kg',
                'description' => "Provende équilibrée pour poules pondeuses.",
                'prix_unitaire' => 18000,
                'quantite' => 100,
                'poids' => null,
                'mois' => null,
                'unite_de_mesure' => 'sac',
                'image_1' => 'annonces/provende1.jpg',
                'image_2' => null,
                'statut' => 'visible',
            ],
            [
                'id_utilisateur' => $vendeursAccessoire[0],
                'type_annonce' => 'accessoire',
                'titre' => 'Mangeoires automatiques (lot de 10)',
                'description' => "Mangeoires en plastique robuste pour volaille.",
                'prix_unitaire' => 2500,
                'quantite' => 30,
                'poids' => null,
                'mois' => null,
                'unite_de_mesure' => 'autre',
                'image_1' => 'annonces/mangeoire1.jpg',
                'image_2' => null,
                'statut' => 'visible',
            ],
        ];

        foreach ($annonces as $annonce) {
            DB::table('annonces')->insert(array_merge($annonce, [
                'date_publication' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
