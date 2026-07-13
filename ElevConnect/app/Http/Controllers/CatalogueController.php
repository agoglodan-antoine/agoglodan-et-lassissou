<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Catalogue public des annonces visibles (post-modération), avec recherche
 * par type, prix et proximité — la localisation (latitude/longitude) est
 * commune à tous les rôles et vit sur UTILISATEURS (voir sa migration) ;
 * seule la note moyenne reste propre à chaque table de profil fournisseur
 * (ELEVEURS / VENDEUR_PROVENDE / VENDEUR_ACCESSOIRE), d'où le COALESCE ici.
 * La distance est calculée en SQL classique (formule de Haversine, en
 * kilomètres) plutôt qu'avec un type spatial POINT, pour rester compatible
 * avec toute version de MySQL sans dépendre d'une extension géométrique.
 */
class CatalogueController extends Controller
{
    public function index(Request $request): View
    {
        $lat = $request->float('lat');
        $lng = $request->float('lng');
        $rayon = (int) $request->input('rayon', 50);

        $query = DB::table('annonces as a')
            ->join('utilisateurs as u', 'u.id_utilisateur', '=', 'a.id_utilisateur')
            ->leftJoin('eleveurs as el', 'el.id_utilisateur', '=', 'a.id_utilisateur')
            ->leftJoin('vendeur_provende as vp', 'vp.id_utilisateur', '=', 'a.id_utilisateur')
            ->leftJoin('vendeur_accessoire as va', 'va.id_utilisateur', '=', 'a.id_utilisateur')
            ->where('a.statut', Annonce::STATUT_VISIBLE)
            ->where('a.etat', 'disponible')
            ->whereNull('a.deleted_at')
            ->select([
                'a.id_annonce', 'a.type_annonce', 'a.titre', 'a.description', 'a.prix_unitaire',
                'a.quantite', 'a.poids', 'a.mois', 'a.unite_de_mesure', 'a.image_1', 'a.date_publication',
                'u.nom', 'u.prenom', 'u.adresse',
                DB::raw('COALESCE(el.note_moyenne, vp.note_moyenne, va.note_moyenne) as note_moyenne'),
            ]);

        if ($request->filled('type')) {
            $query->where('a.type_annonce', $request->input('type'));
        }

        if ($request->filled('prix_max')) {
            $query->where('a.prix_unitaire', '<=', $request->float('prix_max'));
        }

        if ($lat && $lng) {
            // Formule de Haversine : distance en km entre (lat, lng) et la
            // position du fournisseur (u.latitude / u.longitude).
            $query->selectRaw(
                '(6371 * acos(
                    cos(radians(?)) * cos(radians(u.latitude))
                    * cos(radians(u.longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(u.latitude))
                )) as distance_km',
                [$lat, $lng, $lat]
            )->having('distance_km', '<=', $rayon)
                ->orderBy('distance_km');
        } else {
            $query->orderByDesc('a.date_publication');
        }

        $annonces = $query->paginate(12)->withQueryString();

        return view('catalogue.index', [
            'annonces' => $annonces,
            'filtres' => $request->only(['type', 'prix_max', 'rayon']),
            'geolocalise' => (bool) ($lat && $lng),
        ]);
    }

    public function show(Annonce $annonce): View
    {
        abort_unless($annonce->statut === Annonce::STATUT_VISIBLE, 404);

        $annonce->load(['auteur', 'reductions']);

        return view('catalogue.show', compact('annonce'));
    }
}
