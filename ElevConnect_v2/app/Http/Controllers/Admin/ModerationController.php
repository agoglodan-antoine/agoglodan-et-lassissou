<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Notifications\ElevConnectNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * File de modération des annonces (rôle Administrateur uniquement).
 * Toute décision (approbation / rejet) génère une notification à destination
 * du fournisseur, conformément à l'exigence "Notifications" du cahier des
 * charges.
 */
class ModerationController extends Controller
{
    public function index(Request $request): View
    {
        $annonces = Annonce::with('auteur')
            ->where('statut', Annonce::STATUT_EN_ATTENTE)
            ->oldest('date_publication')
            ->paginate(15);

        return view('admin.moderation.index', compact('annonces'));
    }

    public function approuver(Request $request, Annonce $annonce): RedirectResponse
    {
        $annonce->update(['statut' => Annonce::STATUT_VISIBLE, 'motif_rejet' => null]);

        $annonce->auteur->notify(new ElevConnectNotification(
            contenu: "Votre annonce « {$annonce->titre} » a été approuvée et est désormais visible.",
            type: 'annonce_approuvee',
            actionText: "Voir l'annonce",
            actionUrl: route('catalogue.show', $annonce),
        ));

        return back()->with('status', "Annonce « {$annonce->titre} » approuvée.");
    }

    public function rejeter(Request $request, Annonce $annonce): RedirectResponse
    {
        $data = $request->validate([
            'motif_rejet' => ['required', 'string', 'max:255'],
        ]);

        $annonce->update(['statut' => Annonce::STATUT_REJETEE, 'motif_rejet' => $data['motif_rejet']]);

        $annonce->auteur->notify(new ElevConnectNotification(
            contenu: "Votre annonce « {$annonce->titre} » a été rejetée. Motif : {$data['motif_rejet']}",
            type: 'annonce_rejetee',
            actionText: "Modifier l'annonce",
            actionUrl: route('mon-espace.annonces.edit', $annonce),
        ));

        return back()->with('status', "Annonce « {$annonce->titre} » rejetée.");
    }
}
