<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Paiement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Paiement en ligne d'une commande, détenu en séquestre par ElevConnect
 * jusqu'à la confirmation de réception (scan du QR code — Phase 4).
 *
 * NOTE D'IMPLÉMENTATION : aucune passerelle Mobile Money / carte bancaire
 * réelle n'est intégrée ici (le mémoire ne spécifie pas de prestataire de
 * paiement précis). Le traitement est simulé de façon synchrone et marqué
 * `reussi` immédiatement ; en production, ce contrôleur ferait un appel à la
 * passerelle choisie (ex. Kkiapay, Fedapay, MTN/Moov Mobile Money) et
 * traiterait sa réponse (webhook) avant de faire passer `statut_paiement`
 * à `reussi` ou `echoue`.
 */
class PaiementController extends Controller
{
    public function show(Request $request, Commande $commande): View
    {
        $this->authorize('view', $commande);

        abort_unless($commande->statut === Commande::EN_ATTENTE, 404,
            "Cette commande n'est plus en attente de paiement.");

        $commande->load('annonce');

        return view('paiement.show', compact('commande'));
    }

    public function process(Request $request, Commande $commande): RedirectResponse
    {
        $this->authorize('view', $commande);

        abort_unless($commande->statut === Commande::EN_ATTENTE, 404);

        $data = $request->validate([
            'moyen_de_paiement' => ['required', Rule::in(config('elevconnect.moyens_paiement'))],
            'numero_de_compte' => ['required', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($commande, $data) {
            $repartition = Paiement::calculerCommission($commande->montant_net_commande);

            Paiement::create([
                'id_commande' => $commande->id_commande,
                'montant_net_commande' => $commande->montant_net_commande,
                'montant_net_livraison' => 0,
                'total_general' => $commande->montant_net_commande,
                'moyen_de_paiement' => $data['moyen_de_paiement'],
                'numero_de_compte' => $data['numero_de_compte'],
                'commission_sur_commande' => $repartition['commission'],
                'commission_sur_livraison' => 0,
                'total_commission' => $repartition['commission'],
                'montant_a_verser_au_fournisseur' => $repartition['montant_a_verser'],
                'montant_a_verser_au_livreur' => 0,
                'statut_paiement' => 'reussi',
                'date_paiement' => now(),
            ]);

            $commande->update(['statut' => Commande::PAYEE]);
        });

        return redirect()->route('commandes.show', $commande)
            ->with('status', 'Paiement effectué avec succès. Les fonds sont détenus en séquestre jusqu\'à la livraison.');
    }
}
