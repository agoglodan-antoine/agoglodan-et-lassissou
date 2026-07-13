<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\Annonce;
use App\Models\Livreur;
use App\Models\ServiceVeterinaire;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Recherche générale, accessible depuis la barre de navigation (modal).
 * Résultats catégorisés en quatre familles, conformément à la demande :
 * annonces, actualités, services vétérinaires, services de transport
 * (profils Livreur).
 */
class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));

        $annonces = collect();
        $actualites = collect();
        $services = collect();
        $livreurs = collect();

        if ($q !== '') {
            $annonces = Annonce::with('auteur')
                ->where('statut', Annonce::STATUT_VISIBLE)
                ->where(function ($query) use ($q) {
                    $query->where('titre', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                })
                ->latest('date_publication')
                ->take(12)
                ->get();

            $actualites = Actualite::with('auteur')
                ->where(function ($query) use ($q) {
                    $query->where('titre', 'like', "%{$q}%")
                        ->orWhere('contenu', 'like', "%{$q}%");
                })
                ->latest('date_publication')
                ->take(12)
                ->get();

            $services = ServiceVeterinaire::with('veterinaire.utilisateur')
                ->where('statut_service', 'disponible')
                ->where(function ($query) use ($q) {
                    $query->where('titre_service', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                })
                ->take(12)
                ->get();

            $livreurs = Livreur::with('utilisateur')
                ->where(function ($query) use ($q) {
                    $query->where('zone_couverture', 'like', "%{$q}%")
                        ->orWhere('moyen_transport', 'like', "%{$q}%");
                })
                ->take(12)
                ->get();
        }

        return view('recherche.index', [
            'q' => $q,
            'annonces' => $annonces,
            'actualites' => $actualites,
            'services' => $services,
            'livreurs' => $livreurs,
            'total' => $annonces->count() + $actualites->count() + $services->count() + $livreurs->count(),
        ]);
    }
}
