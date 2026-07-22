<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Livraison;
use App\Models\Utilisateur;
use App\Notifications\ElevConnectNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Traitement des commandes reçues par un fournisseur (Éleveur / Vendeur de
 * provende / Vendeur d'accessoires) sur ses propres annonces.
 * Cycle couvert ici : payee -> en_cours_de_traitement -> validee (ou annulee).
 * La validation ouvre une entrée LIVRAISON assignée au livreur choisi par
 * l'acheteur à la commande (COMMANDES.id_livreur_souhaite), qui devra
 * l'accepter ou la refuser (module Livraison, Phase 4). En cas de retrait
 * direct (aucun livreur souhaité), aucune LIVRAISON n'est créée : la
 * commande reste `validee` et peut être confirmée directement par
 * l'acheteur (voir CommandeController::confirmerReception()).
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

    public function show(Request $request, Commande $commande): View
    {
        $this->authorize('traiter', $commande);

        $commande->load('annonce.auteur', 'acheteur', 'paiement', 'livraison');

        return view('commandes.fournisseur-show', compact('commande'));
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

            if ($commande->estRetraitDirect()) {
                $commande->acheteur->notify(new ElevConnectNotification(
                    contenu: "Votre commande #{$commande->id_commande} a été validée par le fournisseur. Vous pouvez la retirer directement, puis confirmer la réception avec votre code.",
                    type: 'commande',
                    actionText: 'Voir ma commande',
                    actionUrl: route('mon-espace.commandes.show', $commande),
                ));

                return;
            }

            Livraison::create([
                'id_commande' => $commande->id_commande,
                'id_livreur' => $commande->id_livreur_souhaite,
                'adresse_fournisseur' => $commande->annonce->auteur->adresse ?? 'Non renseignée',
                'adresse_client' => $commande->acheteur->adresse ?? 'Non renseignée',
                'statut' => Livraison::STATUT_EN_ATTENTE,
                'verification_authenticite' => 'en_attente',
            ]);

            $commande->acheteur->notify(new ElevConnectNotification(
                contenu: "Votre commande #{$commande->id_commande} a été validée par le fournisseur et proposée au livreur choisi.",
                type: 'commande',
                actionText: 'Voir ma commande',
                actionUrl: route('mon-espace.commandes.show', $commande),
            ));

            Utilisateur::find($commande->id_livreur_souhaite)?->notify(new ElevConnectNotification(
                contenu: "Une nouvelle livraison vous est proposée pour la commande #{$commande->id_commande}.",
                type: 'livraison',
                actionText: 'Voir les livraisons proposées',
                actionUrl: route('mon-espace.livraison.proposees'),
            ));
        });

        $message = $commande->fresh()->estRetraitDirect()
            ? 'Commande validée — retrait direct, sans livreur.'
            : 'Commande validée — proposée au livreur choisi par l\'acheteur.';

        return back()->with('status', $message);
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

            $commande->load('acheteur');
            $commande->acheteur->notify(new ElevConnectNotification(
                contenu: "Votre commande #{$commande->id_commande} a été annulée par le fournisseur et remboursée. Motif : {$data['motif_de_rejet']}",
                type: 'commande',
                actionText: 'Voir ma commande',
                actionUrl: route('mon-espace.commandes.show', $commande),
            ));
        });

        return back()->with('status', 'Commande refusée, acheteur remboursé et notifié.');
    }
}
