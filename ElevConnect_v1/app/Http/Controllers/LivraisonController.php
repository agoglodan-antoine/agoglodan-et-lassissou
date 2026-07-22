<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Livraison;
use App\Models\NotificationElevConnect;
use App\Models\Paiement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Espace Livreur : livraisons qui lui sont proposées (assignées par
 * l'acheteur à la commande) et suivi de ses propres livraisons, jusqu'à la
 * remise au client (la confirmation finale par code QR est faite côté
 * Acheteur — voir CommandeController::confirmerReception()).
 *
 * Une livraison n'est jamais un pool ouvert : elle est assignée à un
 * livreur précis (id_livreur) dès sa création. En cas de refus, elle est
 * automatiquement reproposée au livreur disponible le plus proche du point
 * d'enlèvement (voir Livraison::trouverProchainCandidat()).
 */
class LivraisonController extends Controller
{
    public function proposees(Request $request): View
    {
        abort_unless($request->user()->livreur, 403, "Réservé aux livreurs.");

        $livraisons = Livraison::with('commande.annonce.auteur', 'commande.acheteur')
            ->where('id_livreur', $request->user()->id_utilisateur)
            ->where('statut', Livraison::STATUT_EN_ATTENTE)
            ->latest('updated_at')
            ->paginate(15);

        return view('livraison.proposees', compact('livraisons'));
    }

    public function mesLivraisons(Request $request): View
    {
        abort_unless($request->user()->livreur, 403, "Réservé aux livreurs.");

        $livraisons = $request->user()->livreur->livraisons()
            ->whereIn('statut', [Livraison::STATUT_PRISE_EN_CHARGE, Livraison::STATUT_EN_COURS, Livraison::STATUT_TERMINEE])
            ->with('commande.annonce.auteur', 'commande.acheteur')
            ->latest('updated_at')
            ->paginate(15);

        return view('livraison.mes-livraisons', compact('livraisons'));
    }

    public function show(Request $request, Livraison $livraison): View
    {
        abort_unless($livraison->id_livreur === $request->user()->id_utilisateur, 403);

        $livraison->load('commande.annonce.auteur', 'commande.acheteur');

        return view('livraison.show', compact('livraison'));
    }

    public function accepter(Request $request, Livraison $livraison): RedirectResponse
    {
        $this->authorize('accepter', $livraison);

        $data = $request->validate([
            'frais_de_livraison' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $livraison, $data) {
            // Verrouillage optimiste : la livraison assignée à ce livreur ne
            // peut être acceptée qu'une fois, et par lui uniquement.
            $livraison = Livraison::where('id_livraison', $livraison->id_livraison)
                ->where('id_livreur', $request->user()->id_utilisateur)
                ->where('statut', Livraison::STATUT_EN_ATTENTE)
                ->lockForUpdate()
                ->firstOrFail();

            $livreur = $request->user()->livreur()->with('reductions')->first();
            $commande = $livraison->commande;

            $detail = Livraison::calculerFrais($livreur, (float) $data['frais_de_livraison'], $commande->quantite);
            $repartitionCommission = Paiement::calculerCommission($detail['montant_net_livraison']);

            $livraison->update([
                'frais_de_livraison' => $detail['frais_de_livraison'],
                'reduction_sur_frais' => $detail['reduction_sur_frais'],
                'montant_net_livraison' => $detail['montant_net_livraison'],
                'statut' => Livraison::STATUT_PRISE_EN_CHARGE,
            ]);

            $commande->update(['statut' => Commande::EN_COURS_DE_LIVRAISON]);

            // Le paiement (séquestre) est mis à jour avec la part "livraison" de la commission.
            if ($paiement = $commande->paiement) {
                $paiement->update([
                    'montant_net_livraison' => $detail['montant_net_livraison'],
                    'commission_sur_livraison' => $repartitionCommission['commission'],
                    'total_commission' => $paiement->commission_sur_commande + $repartitionCommission['commission'],
                    'montant_a_verser_au_livreur' => $repartitionCommission['montant_a_verser'],
                    'total_general' => $paiement->montant_net_commande + $detail['montant_net_livraison'],
                ]);
            }

            NotificationElevConnect::create([
                'id_utilisateur' => $commande->id_acheteur,
                'contenu' => "Votre commande #{$commande->id_commande} a été prise en charge par le livreur choisi.",
                'type' => 'livraison',
                'date_creation' => now(),
            ]);
        });

        return redirect()->route('mon-espace.livraisons.mes')->with('status', 'Livraison acceptée.');
    }

    /**
     * Refus d'une livraison proposée : conserve l'historique du refus et
     * repropose automatiquement au livreur disponible le plus proche du
     * point d'enlèvement. Si aucun candidat n'est trouvé, l'acheteur et le
     * fournisseur sont notifiés pour qu'un autre choix soit fait (nouveau
     * livreur ou retrait direct).
     */
    public function rejeter(Request $request, Livraison $livraison): RedirectResponse
    {
        $this->authorize('accepter', $livraison);

        DB::transaction(function () use ($request, $livraison) {
            $livraison = Livraison::where('id_livraison', $livraison->id_livraison)
                ->where('id_livreur', $request->user()->id_utilisateur)
                ->where('statut', Livraison::STATUT_EN_ATTENTE)
                ->lockForUpdate()
                ->firstOrFail();

            $commande = $livraison->commande()->with('annonce.auteur')->first();
            $fournisseur = $commande->annonce->auteur;

            $refus = $livraison->livreurs_ayant_refuse ?? [];
            $refus[] = $request->user()->id_utilisateur;

            $prochain = ($fournisseur->latitude && $fournisseur->longitude)
                ? $livraison->trouverProchainCandidat($fournisseur->latitude, $fournisseur->longitude)
                : null;

            if ($prochain) {
                $livraison->update([
                    'id_livreur' => $prochain->id_utilisateur,
                    'statut' => Livraison::STATUT_EN_ATTENTE,
                    'livreurs_ayant_refuse' => $refus,
                ]);

                NotificationElevConnect::create([
                    'id_utilisateur' => $prochain->id_utilisateur,
                    'contenu' => "Une livraison vous est proposée pour la commande #{$commande->id_commande}.",
                    'type' => 'livraison',
                    'date_creation' => now(),
                ]);
            } else {
                $livraison->update([
                    'id_livreur' => null,
                    'statut' => Livraison::STATUT_REJETEE,
                    'livreurs_ayant_refuse' => $refus,
                ]);

                NotificationElevConnect::create([
                    'id_utilisateur' => $commande->id_acheteur,
                    'contenu' => "Aucun livreur disponible n'a accepté votre commande #{$commande->id_commande}. Choisissez un autre livreur ou optez pour un retrait direct.",
                    'type' => 'livraison',
                    'date_creation' => now(),
                ]);
            }
        });

        return redirect()->route('mon-espace.livraisons.proposees')->with('status', 'Livraison refusée.');
    }

    public function demarrer(Request $request, Livraison $livraison): RedirectResponse
    {
        $this->authorize('gerer', $livraison);
        abort_unless($livraison->statut === Livraison::STATUT_PRISE_EN_CHARGE, 422);

        $livraison->update(['statut' => Livraison::STATUT_EN_COURS]);

        return back()->with('status', 'Livraison en cours de route.');
    }

    public function livrer(Request $request, Livraison $livraison): RedirectResponse
    {
        $this->authorize('gerer', $livraison);
        abort_unless($livraison->statut === Livraison::STATUT_EN_COURS, 422,
            "Marquez d'abord la livraison comme en cours de route.");

        DB::transaction(function () use ($livraison) {
            $commande = $livraison->commande;
            $commande->update(['statut' => Commande::LIVREE]);

            NotificationElevConnect::create([
                'id_utilisateur' => $commande->id_acheteur,
                'contenu' => "Votre commande #{$commande->id_commande} a été livrée. Scannez le code QR pour confirmer la réception.",
                'type' => 'livraison',
                'date_creation' => now(),
            ]);
        });

        return back()->with('status', "Livraison marquée comme remise. En attente de confirmation par l'acheteur (scan du code QR).");
    }
}
