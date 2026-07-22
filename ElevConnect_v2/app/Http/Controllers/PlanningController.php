<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanningRequest;
use App\Models\PlanningLivreur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Planning de disponibilité du livreur (cahier des charges, règle de gestion
 * n°10 : « Un livreur peut [...] définir [...] un planning de disponibilité
 * qu'il peut ajuster »). Un créneau marqué indisponible retire le livreur des
 * candidats proposés à l'acheteur pour la durée du créneau — voir
 * Livreur::candidatsProches() / estDisponibleMaintenant().
 */
class PlanningController extends Controller
{
    public function index(Request $request): View
    {
        $creneaux = $request->user()->livreur->planning()->orderBy('date_debut')->get();

        return view('planning.index', compact('creneaux'));
    }

    public function store(StorePlanningRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $request->user()->livreur->planning()->create([
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
            'disponible' => false,
        ]);

        return back()->with('status', 'Créneau d\'indisponibilité ajouté.');
    }

    public function destroy(Request $request, PlanningLivreur $planning): RedirectResponse
    {
        abort_unless($planning->id_livreur === $request->user()->id_utilisateur, 403);

        $planning->delete();

        return back()->with('status', 'Créneau supprimé.');
    }
}
