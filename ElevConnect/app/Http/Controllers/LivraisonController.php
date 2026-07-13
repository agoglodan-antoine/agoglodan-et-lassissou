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
 * Espace Livreur : livraisons disponibles (non affectées) et suivi de ses
 * propres livraisons, jusqu'à la remise au client (la confirmation finale
 * par code QR est faite côté Acheteur — voir CommandeController::confirmerReception()).
 */
class LivraisonController extends Controller
{
    public function disponibles(Request $request): View
    {
        $livraisons = Livraison::with('commande.annonce.auteur', 'commande.acheteur')
            ->whereNull('id_livreur')
            ->where('statut', Livraison::STATUT_EN_ATTENTE)
            ->latest('created_at')
            ->paginate(15);

        return view('livraison.disponibles', compact('livraisons'));
    }

    public function mesLivraisons(Request $request): View
    {
        abort_unless($request->user()->livreur, 403, "Réservé aux livreurs.");

        $livraisons = $request->user()->livreur->livraisons()
            ->with('commande.annonce.auteur', 'commande.acheteur')
            ->latest('updated_at')
            ->paginate(15);

        return view('livraison.mes-livraisons', compact('livraisons'));
    }

    public function accepter(Request $request, Livraison $livraison): RedirectResponse
    {
        $this->authorize('accepter', $livraison);

        $data = $request->validate([
            'frais_de_livraison' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $livraison, $data) {
            // Verrouillage optimiste : une livraison ne peut être acceptée qu'une fois.
            $livraison = Livraison::where('id_livraison', $livraison->id_livraison)
                ->whereNull('id_livreur')
                ->lockForUpdate()
                ->firstOrFail();

            $livreur = $request->user()->livreur()->with('reductions')->first();
            $commande = $livraison->commande;

            $detail = Livraison::calculerFrais($livreur, (float) $data['frais_de_livraison'], $commande->quantite);
            $repartitionCommission = Paiement::calculerCommission($detail['montant_net_livraison']);

            $livraison->update([
                'id_livreur' => $request->user()->id_utilisateur,
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
                'contenu' => "Votre commande #{$commande->id_commande} a été prise en charge par un livreur.",
                'type' => 'livraison',
                'date_creation' => now(),
            ]);
        });

        return redirect()->route('livraison.mes')->with('status', 'Livraison acceptée.');
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
