<?php

namespace App\Http\Controllers;

use App\Models\NotificationElevConnect;
use App\Models\RendezVous;
use App\Models\Veterinaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Rendez-vous vétérinaires : pris en ligne par l'Éleveur, mais **payés hors
 * plateforme** directement entre les deux parties (cf. cahier des charges —
 * aucune intégration de paiement ici, volontairement).
 */
class RendezVousController extends Controller
{
    public function create(Request $request, Veterinaire $veterinaire): View
    {
        $this->authorize('create', RendezVous::class);

        $veterinaire->load(['services' => fn ($q) => $q->where('statut_service', 'disponible'), 'utilisateur']);

        return view('rendez-vous.create', compact('veterinaire'));
    }

    public function store(Request $request, Veterinaire $veterinaire): RedirectResponse
    {
        $this->authorize('create', RendezVous::class);

        $data = $request->validate([
            'id_service' => ['nullable', 'exists:services_veterinaires,id_service'],
            'sujet' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'date_prevue' => ['required', 'date', 'after:now'],
        ]);

        $rdv = RendezVous::create([
            'id_eleveur' => $request->user()->id_utilisateur,
            'id_veterinaire' => $veterinaire->id_utilisateur,
            'id_service' => $data['id_service'] ?? null,
            'sujet' => $data['sujet'],
            'description' => $data['description'] ?? null,
            'date_prevue' => $data['date_prevue'],
            'statut' => RendezVous::EN_ATTENTE,
            'date_creation' => now(),
        ]);

        NotificationElevConnect::create([
            'id_utilisateur' => $veterinaire->id_utilisateur,
            'contenu' => "Nouvelle demande de rendez-vous de la part d'un éleveur pour le {$rdv->date_prevue->format('d/m/Y à H:i')}.",
            'type' => 'rendez_vous',
            'date_creation' => now(),
        ]);

        return redirect()->route('mon-espace.rendez-vous.index')
            ->with('status', 'Demande de rendez-vous envoyée. Le paiement de la consultation se fait directement avec le vétérinaire.');
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->eleveur, 403, "Réservé aux éleveurs.");

        $rdvs = $request->user()->eleveur->rendezVous()
            ->with('veterinaire.utilisateur', 'service')
            ->latest('date_prevue')
            ->paginate(12);

        return view('rendez-vous.index', compact('rdvs'));
    }

    public function recus(Request $request): View
    {
        abort_unless($request->user()->veterinaire, 403, "Réservé aux vétérinaires.");

        $rdvs = $request->user()->veterinaire->rendezVous()
            ->with('eleveur.utilisateur', 'service')
            ->latest('date_prevue')
            ->paginate(12);

        return view('rendez-vous.recus', compact('rdvs'));
    }

    public function show(RendezVous $rendezVous): View
    {
        $this->authorize('view', $rendezVous);

        $rendezVous->load('eleveur.utilisateur', 'veterinaire.utilisateur', 'service');

        return view('rendez-vous.show', compact('rendezVous'));
    }

    public function annuler(RendezVous $rendezVous): RedirectResponse
    {
        $this->authorize('annuler', $rendezVous);

        $rendezVous->update(['statut' => RendezVous::ANNULE]);

        return back()->with('status', 'Rendez-vous annulé.');
    }

    public function confirmer(RendezVous $rendezVous): RedirectResponse
    {
        $this->authorize('traiter', $rendezVous);
        abort_unless($rendezVous->statut === RendezVous::EN_ATTENTE, 422);

        $rendezVous->update(['statut' => RendezVous::CONFIRME]);

        NotificationElevConnect::create([
            'id_utilisateur' => $rendezVous->id_eleveur,
            'contenu' => "Votre rendez-vous du {$rendezVous->date_prevue->format('d/m/Y à H:i')} a été confirmé par le vétérinaire.",
            'type' => 'rendez_vous',
            'date_creation' => now(),
        ]);

        return back()->with('status', 'Rendez-vous confirmé.');
    }

    public function refuser(Request $request, RendezVous $rendezVous): RedirectResponse
    {
        $this->authorize('traiter', $rendezVous);
        abort_unless($rendezVous->statut === RendezVous::EN_ATTENTE, 422);

        $rendezVous->update(['statut' => RendezVous::REFUSE]);

        NotificationElevConnect::create([
            'id_utilisateur' => $rendezVous->id_eleveur,
            'contenu' => "Votre demande de rendez-vous du {$rendezVous->date_prevue->format('d/m/Y à H:i')} a été refusée par le vétérinaire.",
            'type' => 'rendez_vous',
            'date_creation' => now(),
        ]);

        return back()->with('status', 'Rendez-vous refusé.');
    }

    public function marquerRealise(RendezVous $rendezVous): RedirectResponse
    {
        $this->authorize('traiter', $rendezVous);
        abort_unless($rendezVous->statut === RendezVous::CONFIRME, 422);

        $rendezVous->update(['statut' => RendezVous::REALISE]);

        return back()->with('status', 'Rendez-vous marqué comme réalisé.');
    }
}
