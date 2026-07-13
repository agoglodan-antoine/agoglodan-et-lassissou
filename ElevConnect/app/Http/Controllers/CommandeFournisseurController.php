<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Livraison;
use App\Models\NotificationElevConnect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Traitement des commandes reçues par un fournisseur (Éleveur / Vendeur de
 * provende / Vendeur d'accessoires) sur ses propres annonces.
 * Cycle couvert ici : payee -> en_cours_de_traitement -> validee (ou annulee).
 * La validation ouvre automatiquement une entrée LIVRAISON, prise en charge
 * ensuite par un livreur (module Livraison, Phase 4).
 */
class CommandeFournisseurController extends Controller
{
    public function index(Request $request): View
    {
        $commandes = Commande::whereHas('annonce', function ($q) use ($request) {
                $q->where('id_utilisateur', $request->user()->id_utilisateur);
            })
            ->with('annonce', 'acheteur')
            ->whereIn('statut', [Commande::PAYEE, Commande::EN_COURS_DE_TRAITEMENT, Commande::VALIDEE])
            ->latest('date_commande')
            ->paginate(15);

        return view('commandes.fournisseur-index', compact('commandes'));
    }

    public function prendreEnCharge(Request $request, Commande $commande): RedirectResponse
    {
        $this->authorize('traiter', $commande);
        abort_unless($commande->statut === Commande::PAYEE, 422, "Cette commande n'est pas en attente de traitement.");

        $commande->update(['statut' => Commande::EN_COURS_DE_TRAITEMENT]);

        return back()->with('status', 'Commande prise en charge.');
    }

    public function valider(Request $request, Commande $commande): RedirectResponse
    {
        $this->authorize('traiter', $commande);
        abort_unless($commande->statut === Commande::EN_COURS_DE_TRAITEMENT, 422,
            "Cette commande doit d'abord être prise en charge.");

        DB::transaction(function () use ($request, $commande) {
            $commande->update(['statut' => Commande::VALIDEE]);
            $commande->load('annonce.auteur', 'acheteur');

            Livraison::create([
                'id_commande' => $commande->id_commande,
                'adresse_fournisseur' => $commande->annonce->auteur->adresse ?? 'Non renseignée',
                'adresse_client' => $commande->acheteur->adresse ?? 'Non renseignée',
                'statut' => Livraison::STATUT_EN_ATTENTE,
                'verification_authenticite' => 'en_attente',
            ]);

            NotificationElevConnect::create([
                'id_utilisateur' => $commande->id_acheteur,
                'contenu' => "Votre commande #{$commande->id_commande} a été validée par le fournisseur et est proposée aux livreurs disponibles.",
                'type' => 'commande',
                'date_creation' => now(),
            ]);
        });

        return back()->with('status', 'Commande validée — elle est désormais proposée aux livreurs disponibles.');
    }

    public function refuser(Request $request, Commande $commande): RedirectResponse
    {
        $this->authorize('traiter', $commande);
        abort_unless(
            in_array($commande->statut, [Commande::PAYEE, Commande::EN_COURS_DE_TRAITEMENT], true),
            422,
            "Cette commande ne peut plus être refusée à ce stade."
        );

        $data = $request->validate(['motif_de_rejet' => ['required', 'string', 'max:255']]);

        DB::transaction(function () use ($commande, $data) {
            $commande->update([
                'statut' => Commande::ANNULEE,
                'motif_de_rejet' => $data['motif_de_rejet'],
            ]);

            // Remboursement du séquestre : aucune passerelle réelle n'étant intégrée
            // (cf. PaiementController), on marque simplement le paiement "rembourse".
            $commande->paiement()?->update(['statut_paiement' => 'rembourse']);

            NotificationElevConnect::create([
                'id_utilisateur' => $commande->id_acheteur,
                'contenu' => "Votre commande #{$commande->id_commande} a été annulée par le fournisseur et remboursée. Motif : {$data['motif_de_rejet']}",
                'type' => 'commande',
                'date_creation' => now(),
            ]);
        });

        return back()->with('status', 'Commande refusée, acheteur remboursé et notifié.');
    }
}
