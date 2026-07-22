<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Abonnement vétérinaire — seul rôle éligible (cahier des charges, chap. 2).
 * Basique : gratuit, services limités. Premium : 2 000 FCFA/mois, services
 * illimités, mise en avant, statistiques (voir config('elevconnect')).
 *
 * Comme pour PaiementController, aucune passerelle réelle n'est intégrée :
 * le paiement de l'abonnement est simulé et confirmé immédiatement.
 */
class AbonnementController extends Controller
{
    public function show(Request $request): View
    {
        abort_unless($request->user()->veterinaire, 403, "Réservé aux vétérinaires.");

        $veterinaire = $request->user()->veterinaire;
        $abonnementActif = $veterinaire->abonnementActif;

        return view('abonnement.show', [
            'veterinaire' => $veterinaire,
            'abonnementActif' => $abonnementActif,
            'estPremium' => $veterinaire->estPremium(),
            'historique' => $veterinaire->abonnements()->latest('date_debut')->paginate(10),
        ]);
    }

    public function souscrire(Request $request): RedirectResponse
    {
        abort_unless($request->user()->veterinaire, 403, "Réservé aux vétérinaires.");

        $data = $request->validate([
            'formule' => ['required', Rule::in([Abonnement::BASIQUE, Abonnement::PREMIUM])],
            'moyen_de_paiement' => ['required_if:formule,premium', Rule::in(config('elevconnect.moyens_paiement'))],
            'numero_de_compte' => ['required_if:formule,premium', 'string', 'max:50'],
        ]);

        $veterinaire = $request->user()->veterinaire;

        DB::transaction(function () use ($veterinaire, $data) {
            // Un seul abonnement actif à la fois.
            $veterinaire->abonnements()->where('statut', 'actif')->update(['statut' => 'expire']);

            Abonnement::create([
                'id_veterinaire' => $veterinaire->id_utilisateur,
                'formule' => $data['formule'],
                'date_debut' => now()->toDateString(),
                'date_expiration' => $data['formule'] === Abonnement::PREMIUM
                    ? now()->addMonth()->toDateString()
                    : now()->addYears(10)->toDateString(), // Basique : pas d'expiration effective.
                'statut' => 'actif',
            ]);
        });

        $message = $data['formule'] === Abonnement::PREMIUM
            ? 'Abonnement Premium activé pour 1 mois. Paiement simulé (voir AbonnementController).'
            : 'Formule Basique activée.';

        return redirect()->route('mon-espace.abonnement.show')->with('status', $message);
    }
}
