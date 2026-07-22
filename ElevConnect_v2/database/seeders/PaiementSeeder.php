<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaiementSeeder extends Seeder
{
    public function run(): void
    {
        $commandes = DB::table('commandes')->get();
        $commissionRate = 0.05; // 5 %
        $moyens = ['mobile_money', 'carte_bancaire'];

        foreach ($commandes as $i => $commande) {
            $montantNetLivraison = 1500; // frais de livraison forfaitaire simulé
            $totalGeneral = $commande->montant_net_commande + $montantNetLivraison;
            $commissionCommande = round($commande->montant_net_commande * $commissionRate, 2);
            $commissionLivraison = round($montantNetLivraison * $commissionRate, 2);
            $totalCommission = $commissionCommande + $commissionLivraison;

            DB::table('paiements')->insert([
                'id_commande' => $commande->id_commande,
                'montant_net_commande' => $commande->montant_net_commande,
                'montant_net_livraison' => $montantNetLivraison,
                'total_general' => $totalGeneral,
                'moyen_de_paiement' => $moyens[$i % count($moyens)],
                'numero_de_compte' => '2290' . rand(1000000, 9999999),
                'commission_sur_commande' => $commissionCommande,
                'commission_sur_livraison' => $commissionLivraison,
                'total_commission' => $totalCommission,
                'montant_a_verser_au_fournisseur' => $commande->montant_net_commande - $commissionCommande,
                'montant_a_verser_au_livreur' => $montantNetLivraison - $commissionLivraison,
                'statut_paiement' => 'reussi',
                'date_paiement' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
