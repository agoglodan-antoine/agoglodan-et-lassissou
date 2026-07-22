<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRendezVousRequest;
use App\Models\RendezVous;
use App\Models\Veterinaire;
use App\Notifications\ElevConnectNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function store(StoreRendezVousRequest $request, Veterinaire $veterinaire): RedirectResponse
    {
        $data = $request->validated();

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

        $veterinaire->utilisateur->notify(new ElevConnectNotification(
            contenu: "Nouvelle demande de rendez-vous de la part d'un éleveur pour le {$rdv->date_prevue->format('d/m/Y à H:i')}.",
            type: 'rendez_vous',
            actionText: 'Voir la demande',
            actionUrl: route('mon-espace.rendez-vous.show', $rdv),
        ));

        return redirect()->route('mon-espace.rendez-vous.index')
            ->with('status', 'Demande de rendez-vous envoyée. Le paiement de la consultation se fait directement avec le vétérinaire.');
    }

    public function index(Request $request): View
    {
        $rdvs = $request->user()->eleveur->rendezVous()
            ->with('veterinaire.utilisateur', 'service')
            ->latest('date_prevue')
            ->paginate(12);

        return view('rendez-vous.index', compact('rdvs'));
    }

    public function recus(Request $request): View
    {
        $rdvs = $request->user()->veterinaire->rendezVous()
            ->with('eleveur.utilisateur', 'service')
            ->latest('date_prevue')
            ->paginate(12);

        return view('rendez-vous.recus', compact('rdvs'));
    }

    /**
     * Détail d'un rendez-vous, commun à l'éleveur et au vétérinaire concernés
     * (RendezVousPolicy::view autorise les deux). La vue adapte les actions
     * disponibles (annuler / confirmer / refuser / marquer réalisé) selon
     * l'utilisateur connecté.
     */
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

    /** L'éleveur dépose un avis et une note une fois la consultation réalisée. */
    public function noter(Request $request, RendezVous $rendezVous): RedirectResponse
    {
        $this->authorize('noter', $rendezVous);

        $data = $request->validate([
            'note_client' => ['required', 'integer', 'min:1', 'max:5'],
            'avis_client' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($rendezVous, $data) {
            $rendezVous->update([
                'note_client' => $data['note_client'],
                'avis_client' => $data['avis_client'] ?? null,
            ]);

            $this->mettreAJourNoteMoyenneVeterinaire($rendezVous);
        });

        return back()->with('status', 'Merci pour votre avis !');
    }

    /**
     * Recalcule la note moyenne du vétérinaire à partir de l'ensemble des
     * rendez-vous notés (mécanisme équivalent à
     * CommandeController::mettreAJourNoteMoyenneFournisseur()).
     */
    private function mettreAJourNoteMoyenneVeterinaire(RendezVous $rendezVous): void
    {
        $veterinaire = Veterinaire::find($rendezVous->id_veterinaire);
        if (! $veterinaire) {
            return;
        }

        $stats = RendezVous::where('id_veterinaire', $rendezVous->id_veterinaire)
            ->whereNotNull('note_client')
            ->selectRaw('AVG(note_client) as moyenne, COUNT(*) as total')
            ->first();

        $veterinaire->update([
            'note_moyenne' => round($stats->moyenne, 2),
            'nombre_avis' => $stats->total,
        ]);
    }

    public function confirmer(RendezVous $rendezVous): RedirectResponse
    {
        $this->authorize('traiter', $rendezVous);
        abort_unless($rendezVous->statut === RendezVous::EN_ATTENTE, 422);

        $rendezVous->update(['statut' => RendezVous::CONFIRME]);

        $rendezVous->load('eleveur.utilisateur');
        $rendezVous->eleveur->utilisateur->notify(new ElevConnectNotification(
            contenu: "Votre rendez-vous du {$rendezVous->date_prevue->format('d/m/Y à H:i')} a été confirmé par le vétérinaire.",
            type: 'rendez_vous',
            actionText: 'Voir le rendez-vous',
            actionUrl: route('mon-espace.rendez-vous.show', $rendezVous),
        ));

        return back()->with('status', 'Rendez-vous confirmé.');
    }

    public function refuser(Request $request, RendezVous $rendezVous): RedirectResponse
    {
        $this->authorize('traiter', $rendezVous);
        abort_unless($rendezVous->statut === RendezVous::EN_ATTENTE, 422);

        $rendezVous->update(['statut' => RendezVous::REFUSE]);

        $rendezVous->load('eleveur.utilisateur');
        $rendezVous->eleveur->utilisateur->notify(new ElevConnectNotification(
            contenu: "Votre demande de rendez-vous du {$rendezVous->date_prevue->format('d/m/Y à H:i')} a été refusée par le vétérinaire.",
            type: 'rendez_vous',
            actionText: 'Voir le rendez-vous',
            actionUrl: route('mon-espace.rendez-vous.show', $rendezVous),
        ));

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
