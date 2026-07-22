<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Versement;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Versements effectués par ElevConnect au fournisseur et, le cas échéant,
 * au livreur, une fois la réception d'une commande confirmée (montants nets
 * de commission — voir Paiement::montant_a_verser_au_fournisseur /
 * montant_a_verser_au_livreur). Logique de création extraite de
 * CommandeController::confirmerReception() pour l'isoler dans son propre
 * contrôleur, conformément au chapitre 4 du mémoire.
 */
class VersementController extends Controller
{
    /**
     * Crée les versements dus pour une commande dont la réception vient
     * d'être confirmée. Appelé depuis CommandeController::confirmerReception(),
     * à l'intérieur de la même transaction DB.
     */
    public function creerPourCommande(Commande $commande): void
    {
        $paiement = $commande->paiement;
        if (! $paiement) {
            return;
        }

        // Versement au fournisseur (montant net de la commande, commission déduite).
        Versement::create([
            'id_commande' => $commande->id_commande,
            'id_paiement' => $paiement->id_paiement,
            'type_beneficiaire' => Versement::BENEFICIAIRE_FOURNISSEUR,
            'id_beneficiaire' => $commande->annonce->id_utilisateur,
            'montant_verser' => $paiement->montant_a_verser_au_fournisseur,
            'moyen_de_versement' => $paiement->moyen_de_paiement,
            'numero_de_compte' => $paiement->numero_de_compte,
            'statut_versement' => 'reussi',
            'date_versement' => now(),
        ]);

        // Versement au livreur (montant net de la livraison, commission déduite),
        // uniquement si une livraison a effectivement été prise en charge.
        if ($commande->livraison && $commande->livraison->id_livreur && $paiement->montant_a_verser_au_livreur > 0) {
            Versement::create([
                'id_commande' => $commande->id_commande,
                'id_paiement' => $paiement->id_paiement,
                'type_beneficiaire' => Versement::BENEFICIAIRE_LIVREUR,
                'id_beneficiaire' => $commande->livraison->id_livreur,
                'montant_verser' => $paiement->montant_a_verser_au_livreur,
                'moyen_de_versement' => $paiement->moyen_de_paiement,
                'numero_de_compte' => $paiement->numero_de_compte,
                'statut_versement' => 'reussi',
                'date_versement' => now(),
            ]);
        }
    }

    /** Historique des versements perçus par le fournisseur ou le livreur connecté. */
    public function index(Request $request): View
    {
        $versements = Versement::with('commande.annonce')
            ->where('id_beneficiaire', $request->user()->id_utilisateur)
            ->latest('date_versement')
            ->paginate(20);

        return view('versements.index', compact('versements'));
    }
}
