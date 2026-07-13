<?php

namespace App\Http\Controllers;

use App\Models\ServiceVeterinaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
        abort_unless($request->user()->veterinaire, 403, "Réservé aux vétérinaires.");

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

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ServiceVeterinaire::class);

        $veterinaire = $request->user()->veterinaire;
        abort_if($veterinaire->limiteServicesAtteinte(), 403,
            "Limite de services atteinte pour la formule Basique. Passez en Premium pour publier davantage de services.");

        $data = $request->validate([
            'titre_service' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'prix' => ['required', 'numeric', 'min:0'],
            'temps_traitement' => ['required', 'integer', 'min:5', 'max:600'],
            'photo_illustrative' => ['nullable', 'image', 'max:4096'],
        ]);

        $data['id_veterinaire'] = $veterinaire->id_utilisateur;
        $data['statut_service'] = 'disponible';

        if ($request->hasFile('photo_illustrative')) {
            $data['photo_illustrative'] = $request->file('photo_illustrative')->store('services', 'public');
        }

        ServiceVeterinaire::create($data);

        return redirect()->route('services.index')->with('status', 'Service publié.');
    }

    public function edit(ServiceVeterinaire $service): View
    {
        $this->authorize('update', $service);

        return view('services.edit', compact('service'));
    }

    public function update(Request $request, ServiceVeterinaire $service): RedirectResponse
    {
        $this->authorize('update', $service);

        $data = $request->validate([
            'titre_service' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'prix' => ['required', 'numeric', 'min:0'],
            'temps_traitement' => ['required', 'integer', 'min:5', 'max:600'],
            'statut_service' => ['required', Rule::in(['disponible', 'indisponible'])],
            'photo_illustrative' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('photo_illustrative')) {
            if ($service->photo_illustrative) {
                Storage::disk('public')->delete($service->photo_illustrative);
            }
            $data['photo_illustrative'] = $request->file('photo_illustrative')->store('services', 'public');
        }

        $service->update($data);

        return redirect()->route('services.index')->with('status', 'Service mis à jour.');
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

        return redirect()->route('services.index')->with('status', 'Service supprimé.');
    }
}
