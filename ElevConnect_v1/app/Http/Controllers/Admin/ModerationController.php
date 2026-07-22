<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\NotificationElevConnect;
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
        $this->ensureAdministrateur($request);

        $annonces = Annonce::with('auteur')
            ->where('statut', Annonce::STATUT_EN_ATTENTE)
            ->oldest('date_publication')
            ->paginate(15);

        return view('admin.moderation.index', compact('annonces'));
    }

    public function approuver(Request $request, Annonce $annonce): RedirectResponse
    {
        $this->ensureAdministrateur($request);

        $annonce->update(['statut' => Annonce::STATUT_VISIBLE, 'motif_rejet' => null]);

        NotificationElevConnect::create([
            'id_utilisateur' => $annonce->id_utilisateur,
            'contenu' => "Votre annonce « {$annonce->titre} » a été approuvée et est désormais visible.",
            'type' => 'annonce_approuvee',
            'date_creation' => now(),
        ]);

        return back()->with('status', "Annonce « {$annonce->titre} » approuvée.");
    }

    public function rejeter(Request $request, Annonce $annonce): RedirectResponse
    {
        $this->ensureAdministrateur($request);

        $data = $request->validate([
            'motif_rejet' => ['required', 'string', 'max:255'],
        ]);

        $annonce->update(['statut' => Annonce::STATUT_REJETEE, 'motif_rejet' => $data['motif_rejet']]);

        NotificationElevConnect::create([
            'id_utilisateur' => $annonce->id_utilisateur,
            'contenu' => "Votre annonce « {$annonce->titre} » a été rejetée. Motif : {$data['motif_rejet']}",
            'type' => 'annonce_rejetee',
            'date_creation' => now(),
        ]);

        return back()->with('status', "Annonce « {$annonce->titre} » rejetée.");
    }

    /**
     * Garde d'accès explicite au rôle Administrateur.
     * NOTE : à terme, ce contrôle devrait être porté par un middleware
     * `role:administrateur` enregistré dans bootstrap/app.php (voir
     * README_ROADMAP.md) ; il est fait ici en ligne pour que le module
     * fonctionne sans dépendre du fichier de bootstrap non livré dans ce
     * bac à sable.
     */
    private function ensureAdministrateur(Request $request): void
    {
        abort_unless(
            $request->user() && $request->user()->role === \App\Models\Utilisateur::ROLE_ADMINISTRATEUR,
            403,
            "Accès réservé aux administrateurs."
        );
    }
}
