<?php

namespace App\Http\Controllers;

use App\Models\Veterinaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Annuaire public des vétérinaires — recherche par spécialité et proximité
 * (formule de Haversine sur latitude/longitude, voir CatalogueController),
 * fiche profil avec ses services. La mise en avant Premium (config
 * 'elevconnect.abonnement_veterinaire.premium') se traduit ici par un tri
 * prioritaire dans les résultats.
 */
class VeterinaireController extends Controller
{
    public function index(Request $request): View
    {
        $lat = $request->float('lat');
        $lng = $request->float('lng');

        $query = DB::table('veterinaires as v')
            ->join('utilisateurs as u', 'u.id_utilisateur', '=', 'v.id_utilisateur')
            ->leftJoin('abonnements as ab', function ($join) {
                $join->on('ab.id_veterinaire', '=', 'v.id_utilisateur')
                    ->where('ab.statut', 'actif')
                    ->where('ab.formule', 'premium')
                    ->where('ab.date_expiration', '>=', now()->toDateString());
            })
            ->where('u.statut', 'actif')
            ->select([
                'v.id_utilisateur', 'v.specialite', 'v.zone_intervention', 'v.note_moyenne',
                'v.nombre_avis', 'u.nom', 'u.prenom', 'u.adresse',
                DB::raw('ab.id_abonnement is not null as est_premium'),
            ]);

        if ($request->filled('specialite')) {
            $query->where('v.specialite', 'like', '%'.$request->input('specialite').'%');
        }

        if ($lat && $lng) {
            $query->selectRaw(
                '(6371 * acos(
                    cos(radians(?)) * cos(radians(u.latitude)) * cos(radians(u.longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(u.latitude))
                )) as distance_km',
                [$lat, $lng, $lat]
            )->orderByDesc('est_premium')->orderBy('distance_km');
        } else {
            $query->orderByDesc('est_premium')->orderByDesc('v.note_moyenne');
        }

        $veterinaires = $query->paginate(12)->withQueryString();

        return view('veterinaires.index', [
            'veterinaires' => $veterinaires,
            'geolocalise' => (bool) ($lat && $lng),
        ]);
    }

    public function show(Veterinaire $veterinaire): View
    {
        $veterinaire->load(['utilisateur', 'services' => function ($q) {
            $q->where('statut_service', 'disponible');
        }]);

        return view('veterinaires.show', compact('veterinaire'));
    }
}
