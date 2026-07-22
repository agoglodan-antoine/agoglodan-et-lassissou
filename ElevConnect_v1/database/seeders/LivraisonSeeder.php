<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LivraisonSeeder extends Seeder
{
    public function run(): void
    {
        $commandes = DB::table('commandes')->get();
        $livreurs = DB::table('livreurs')->pluck('id_utilisateur')->values();
        $statuts = ['terminee', 'en_cours', 'prise_en_charge', 'terminee'];

        foreach ($commandes as $i => $commande) {
            DB::table('livraison')->insert([
                'id_commande' => $commande->id_commande,
                'id_livreur' => $livreurs[$i % count($livreurs)],
                'adresse_fournisseur' => 'Parakou, Bénin',
                'adresse_client' => 'Cotonou, Bénin',
                'frais_de_livraison' => 1500,
                'reduction_sur_frais' => 0,
                'montant_net_livraison' => 1500,
                'statut' => $statuts[$i % count($statuts)],
                'verification_authenticite' => 'verifiee',
                'date_verification_qr' => now(),
                'description' => "Livraison suivie via QR code d'authenticité.",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
