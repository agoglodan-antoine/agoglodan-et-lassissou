<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VersementSeeder extends Seeder
{
    public function run(): void
    {
        $paiements = DB::table('paiements')->get();
        $annonceProprietaires = DB::table('annonces')->pluck('id_utilisateur', 'id_annonce');
        $commandeAnnonces = DB::table('commandes')->pluck('id_annonce', 'id_commande');
        $livraisonLivreurs = DB::table('livraison')->pluck('id_livreur', 'id_commande');

        foreach ($paiements as $paiement) {
            $idAnnonce = $commandeAnnonces[$paiement->id_commande];
            $idFournisseur = $annonceProprietaires[$idAnnonce];

            // Versement au fournisseur (part de la commande)
            DB::table('versements')->insert([
                'id_commande' => $paiement->id_commande,
                'id_paiement' => $paiement->id_paiement,
                'type_beneficiaire' => 'fournisseur',
                'id_beneficiaire' => $idFournisseur,
                'montant_verser' => $paiement->montant_a_verser_au_fournisseur,
                'moyen_de_versement' => $paiement->moyen_de_paiement,
                'numero_de_compte' => $paiement->numero_de_compte,
                'statut_versement' => 'reussi',
                'date_versement' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Versement au livreur (part de la livraison), si un livreur est associé
            $idLivreur = $livraisonLivreurs[$paiement->id_commande] ?? null;
            if ($idLivreur) {
                DB::table('versements')->insert([
                    'id_commande' => $paiement->id_commande,
                    'id_paiement' => $paiement->id_paiement,
                    'type_beneficiaire' => 'livreur',
                    'id_beneficiaire' => $idLivreur,
                    'montant_verser' => $paiement->montant_a_verser_au_livreur,
                    'moyen_de_versement' => $paiement->moyen_de_paiement,
                    'numero_de_compte' => $paiement->numero_de_compte,
                    'statut_versement' => 'reussi',
                    'date_versement' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
