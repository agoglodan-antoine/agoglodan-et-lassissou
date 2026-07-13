<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommandeSeeder extends Seeder
{
    public function run(): void
    {
        $annonces = DB::table('annonces')->get();
        $acheteurs = DB::table('acheteurs')->pluck('id_utilisateur')->values();

        $statuts = ['confirmee', 'livree', 'en_cours_de_livraison', 'payee'];

        foreach ($annonces as $i => $annonce) {
            $quantite = rand(2, 5);
            $montantTotal = $quantite * $annonce->prix_unitaire;
            $reduction = round($montantTotal * 0.03, 2);
            $montantNet = $montantTotal - $reduction;

            DB::table('commandes')->insert([
                'id_annonce' => $annonce->id_annonce,
                'id_acheteur' => $acheteurs[$i % count($acheteurs)],
                'quantite' => $quantite,
                'prix_unitaire' => $annonce->prix_unitaire,
                'montant_total' => $montantTotal,
                'reduction_sur_commande' => $reduction,
                'montant_net_commande' => $montantNet,
                'statut' => $statuts[$i % count($statuts)],
                'description' => 'Commande passée via la plateforme ElevConnect.',
                'code_authenticite' => strtoupper(Str::random(12)),
                'date_commande' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
