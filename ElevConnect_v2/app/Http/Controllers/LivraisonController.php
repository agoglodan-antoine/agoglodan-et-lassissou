<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Livraison;
use App\Models\Paiement;
use App\Models\Utilisateur;
use App\Notifications\ElevConnectNotification;
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
        $livraisons = Livraison::with('commande.annonce.auteur', 'commande.acheteur')
            ->where('id_livreur', $request->user()->id_utilisateur)
            ->where('statut', Livraison::STATUT_EN_ATTENTE)
            ->latest('updated_at')
            ->paginate(15);

        $estDisponible = $request->user()->livreur->estDisponibleMaintenant();

        return view('livraison.proposees', compact('livraisons', 'estDisponible'));
    }

    public function mesLivraisons(Request $request): View
    {
        $livraisons = $request->user()->livreur->livraisons()
            ->whereIn('statut', [Livraison::STATUT_PRISE_EN_CHARGE, Livraison::STATUT_EN_COURS, Livraison::STATUT_TERMINEE])
            ->with('commande.annonce.auteur', 'commande.acheteur')
            ->latest('updated_at')
            ->paginate(15);

        return view('livraison.mes-livraisons', compact('livraisons'));
    }

    /** Détail d'une livraison (proposée ou en cours), réservé au livreur assigné. */
    public function show(Livraison $livraison): View
    {
        $this->authorize('view', $livraison);

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

            $commande->load('acheteur');
            $commande->acheteur->notify(new ElevConnectNotification(
                contenu: "Votre commande #{$commande->id_commande} a été prise en charge par le livreur choisi.",
                type: 'livraison',
                actionText: 'Voir ma commande',
                actionUrl: route('mon-espace.commandes.show', $commande),
            ));
        });

        return redirect()->route('mon-espace.livraison.mes')->with('status', 'Livraison acceptée.');
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

                Utilisateur::find($prochain->id_utilisateur)?->notify(new ElevConnectNotification(
                    contenu: "Une livraison vous est proposée pour la commande #{$commande->id_commande}.",
                    type: 'livraison',
                    actionText: 'Voir les livraisons proposées',
                    actionUrl: route('mon-espace.livraison.proposees'),
                ));
            } else {
                $livraison->update([
                    'id_livreur' => null,
                    'statut' => Livraison::STATUT_REJETEE,
                    'livreurs_ayant_refuse' => $refus,
                ]);

                $commande->load('acheteur');
                $commande->acheteur->notify(new ElevConnectNotification(
                    contenu: "Aucun livreur disponible n'a accepté votre commande #{$commande->id_commande}. Choisissez un autre livreur ou optez pour un retrait direct.",
                    type: 'livraison',
                    actionText: 'Voir ma commande',
                    actionUrl: route('mon-espace.commandes.show', $commande),
                ));
            }
        });

        return redirect()->route('mon-espace.livraison.proposees')->with('status', 'Livraison refusée.');
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

            $commande->load('acheteur');
            $commande->acheteur->notify(new ElevConnectNotification(
                contenu: "Votre commande #{$commande->id_commande} a été livrée. Scannez le code QR pour confirmer la réception.",
                type: 'livraison',
                actionText: 'Confirmer la réception',
                actionUrl: route('mon-espace.commandes.show', $commande),
            ));
        });

        return redirect()->route('mon-espace.livraison.show', $livraison)
            ->with('status', "Livraison marquée comme remise. Montrez le code QR à l'acheteur pour qu'il confirme la réception.");
    }
}
