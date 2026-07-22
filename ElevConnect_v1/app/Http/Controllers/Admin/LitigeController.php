<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\NotificationElevConnect;
use App\Models\Utilisateur;
use App\Models\Versement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Traitement des litiges (COMMANDES.statut = 'en_litige'), ouverts par
 * l'acheteur lorsqu'il refuse de confirmer la réception d'une commande
 * livrée (cf. CommandeController::signalerProbleme()).
 *
 * Deux issues possibles, tranchées par l'Administrateur :
 *  - en faveur de l'acheteur : remboursement, aucun versement ;
 *  - en faveur du fournisseur : la commande est traitée comme confirmée,
 *    les versements sont déclenchés normalement.
 */
class LitigeController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdministrateur($request);

        $litiges = Commande::with(['annonce.auteur', 'acheteur', 'livraison.livreur', 'paiement'])
            ->where('statut', Commande::EN_LITIGE)
            ->latest('updated_at')
            ->paginate(15);

        return view('admin.litiges.index', compact('litiges'));
    }

    public function resoudreEnFaveurAcheteur(Request $request, Commande $commande): RedirectResponse
    {
        $this->ensureAdministrateur($request);
        abort_unless($commande->statut === Commande::EN_LITIGE, 422);

        DB::transaction(function () use ($commande) {
            $commande->update(['statut' => Commande::REFUSEE]);
            $commande->paiement()?->update(['statut_paiement' => 'rembourse']);

            NotificationElevConnect::create([
                'id_utilisateur' => $commande->id_acheteur,
                'contenu' => "Le litige sur votre commande #{$commande->id_commande} a été tranché en votre faveur : vous êtes remboursé.",
                'type' => 'litige',
                'date_creation' => now(),
            ]);

            NotificationElevConnect::create([
                'id_utilisateur' => $commande->annonce->id_utilisateur,
                'contenu' => "Le litige sur la commande #{$commande->id_commande} a été tranché en faveur de l'acheteur.",
                'type' => 'litige',
                'date_creation' => now(),
            ]);
        });

        return back()->with('status', "Litige tranché en faveur de l'acheteur — remboursement effectué.");
    }

    public function resoudreEnFaveurFournisseur(Request $request, Commande $commande): RedirectResponse
    {
        $this->ensureAdministrateur($request);
        abort_unless($commande->statut === Commande::EN_LITIGE, 422);

        DB::transaction(function () use ($commande) {
            $commande->update(['statut' => Commande::CONFIRMEE]);
            $commande->load('livraison', 'paiement', 'annonce');

            $paiement = $commande->paiement;
            if ($paiement && ! Versement::where('id_commande', $commande->id_commande)->exists()) {
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

            if ($commande->livraison) {
                $commande->livraison->update([
                    'statut' => \App\Models\Livraison::STATUT_TERMINEE,
                    'verification_authenticite' => 'verifiee',
                    'date_verification_qr' => now(),
                ]);
            }

            NotificationElevConnect::create([
                'id_utilisateur' => $commande->id_acheteur,
                'contenu' => "Le litige sur votre commande #{$commande->id_commande} a été tranché en faveur du fournisseur.",
                'type' => 'litige',
                'date_creation' => now(),
            ]);

            NotificationElevConnect::create([
                'id_utilisateur' => $commande->annonce->id_utilisateur,
                'contenu' => "Le litige sur la commande #{$commande->id_commande} a été tranché en votre faveur : le versement a été effectué.",
                'type' => 'litige',
                'date_creation' => now(),
            ]);
        });

        return back()->with('status', 'Litige tranché en faveur du fournisseur — versements effectués.');
    }

    private function ensureAdministrateur(Request $request): void
    {
        abort_unless(
            $request->user() && $request->user()->role === Utilisateur::ROLE_ADMINISTRATEUR,
            403,
            "Accès réservé aux administrateurs."
        );
    }
}
