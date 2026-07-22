<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\ServiceVeterinaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Gestion des services proposés par un vétérinaire (SERVICES_VETERINAIRES).
 * En formule Basique, le nombre de services actifs est limité (voir
 * config('elevconnect.abonnement_veterinaire.basique.limite_services') et
 * Veterinaire::limiteServicesAtteinte() — hypothèse documentée dans le
 * README, le mémoire ne fixant pas de chiffre précis).
 */
class ServiceVeterinaireController extends Controller
{
    public function index(Request $request): View
    {
        $veterinaire = $request->user()->veterinaire;
        $services = $veterinaire->services()->latest()->paginate(12);

        return view('services.index', [
            'services' => $services,
            'veterinaire' => $veterinaire,
            'limiteAtteinte' => $veterinaire->limiteServicesAtteinte(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ServiceVeterinaire::class);

        abort_if($request->user()->veterinaire->limiteServicesAtteinte(), 403,
            "Limite de services atteinte pour la formule Basique. Passez en Premium pour publier davantage de services.");

        return view('services.create');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $veterinaire = $request->user()->veterinaire;
        abort_if($veterinaire->limiteServicesAtteinte(), 403,
            "Limite de services atteinte pour la formule Basique. Passez en Premium pour publier davantage de services.");

        $data = $request->validated();

        $data['id_veterinaire'] = $veterinaire->id_utilisateur;
        $data['statut_service'] = 'disponible';

        if ($request->hasFile('photo_illustrative')) {
            $data['photo_illustrative'] = $request->file('photo_illustrative')->store('services', 'public');
        }

        ServiceVeterinaire::create($data);

        return redirect()->route('mon-espace.services.index')->with('status', 'Service publié.');
    }

    public function show(ServiceVeterinaire $service): View
    {
        $this->authorize('view', $service);

        return view('services.show', compact('service'));
    }

    public function edit(ServiceVeterinaire $service): View
    {
        $this->authorize('update', $service);

        return view('services.edit', compact('service'));
    }

    public function update(UpdateServiceRequest $request, ServiceVeterinaire $service): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo_illustrative')) {
            if ($service->photo_illustrative) {
                Storage::disk('public')->delete($service->photo_illustrative);
            }
            $data['photo_illustrative'] = $request->file('photo_illustrative')->store('services', 'public');
        }

        $service->update($data);

        return redirect()->route('mon-espace.services.index')->with('status', 'Service mis à jour.');
    }

    public function destroy(ServiceVeterinaire $service): RedirectResponse
    {
        $this->authorize('delete', $service);

        if ($service->rendezVous()->exists()) {
            return back()->withErrors(['service' => "Impossible de supprimer : ce service a déjà des rendez-vous associés."]);
        }

        if ($service->photo_illustrative) {
            Storage::disk('public')->delete($service->photo_illustrative);
        }

        $service->delete();

        return redirect()->route('mon-espace.services.index')->with('status', 'Service supprimé.');
    }
}
