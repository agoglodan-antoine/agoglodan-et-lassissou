<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Annonce;
use App\Models\Utilisateur;
use Illuminate\View\View;

/** Page d'accueil publique — hero + vitrine (annonces visibles, acteurs, actualités). */
class HomeController extends Controller
{
    public function index(): View
    {
        $stats = [
            'eleveurs' => Utilisateur::where('role', Utilisateur::ROLE_ELEVEUR)->where('statut', 'actif')->count(),
            'communes' => 0, // TODO phase 2 : distinct sur adresse / reverse-geocoding des localisations
            'annonces' => Annonce::where('statut', Annonce::STATUT_VISIBLE)->count(),
        ];

        $annonces = Annonce::with('auteur')
            ->where('statut', Annonce::STATUT_VISIBLE)
            ->where('etat', 'disponible')
            ->latest('date_publication')
            ->take(8)
            ->get();

        $actualites = Actualite::with('auteur', 'medias')
            ->latest('date_publication')
            ->take(3)
            ->get();

        return view('welcome', compact('stats', 'annonces', 'actualites'));
    }
}
