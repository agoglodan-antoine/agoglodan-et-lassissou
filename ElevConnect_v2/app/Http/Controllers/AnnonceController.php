<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnonceRequest;
use App\Http\Requests\UpdateAnnonceRequest;
use App\Models\Annonce;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Espace fournisseur : publication et gestion des annonces (Éleveur,
 * Vendeur de provende, Vendeur d'accessoires — cf. Utilisateur::ROLES_FOURNISSEURS).
 * Toute annonce nouvellement publiée ou modifiée repasse par la modération
 * de l'Administrateur avant d'être visible du public.
 */
class AnnonceController extends Controller
{
    public function index(Request $request): View
    {
        $annonces = $request->user()->annonces()
            ->withCount('reductions')
            ->latest('date_publication')
            ->paginate(12);

        return view('annonces.index', compact('annonces'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Annonce::class);

        $type = Annonce::typeAttenduPourRole($request->user()->role);

        return view('annonces.create', ['type' => $type]);
    }

    public function store(StoreAnnonceRequest $request): RedirectResponse
    {
        $this->authorize('create', Annonce::class);

        $annonce = DB::transaction(function () use ($request) {
            $annonce = Annonce::create([
                'id_utilisateur' => $request->user()->id_utilisateur,
                'type_annonce' => $request->typeAnnonce(),
                'titre' => $request->input('titre'),
                'description' => $request->input('description'),
                'prix_unitaire' => $request->input('prix_unitaire'),
                'quantite' => $request->input('quantite'),
                'poids' => $request->input('poids'),
                'mois' => $request->input('mois'),
                'unite_de_mesure' => $request->input('unite_de_mesure'),
                'image_1' => $this->enregistrerImage($request, 'image_1'),
                'image_2' => $request->hasFile('image_2') ? $this->enregistrerImage($request, 'image_2') : null,
                'statut' => Annonce::STATUT_EN_ATTENTE,
                'etat' => 'disponible',
                'date_publication' => now(),
            ]);

            $this->synchroniserReductions($annonce, $request->input('reductions', []));

            return $annonce;
        });

        return redirect()->route('mon-espace.annonces.index')
            ->with('status', "Votre annonce « {$annonce->titre} » a été soumise et est en attente de validation par un administrateur.");
    }

    public function show(Annonce $annonce): View
    {
        $this->authorize('view', $annonce);

        $annonce->load('reductions');

        return view('annonces.show', compact('annonce'));
    }

    public function edit(Annonce $annonce): View
    {
        $this->authorize('update', $annonce);

        $annonce->load('reductions');

        // Tableau PHP simple pré-calculé côté serveur — voir la note dans
        // CommandeController::create() sur la fragilité de @json() combiné
        // à une expression chaînée directement dans la vue.
        $reductionsExistantes = [];
        foreach ($annonce->reductions as $reduction) {
            $reductionsExistantes[] = [
                'quantite_min' => $reduction->quantite_min,
                'quantite_max' => $reduction->quantite_max,
                'pourcentage_reduction' => $reduction->pourcentage_reduction,
            ];
        }

        return view('annonces.edit', compact('annonce', 'reductionsExistantes'));
    }

    public function update(UpdateAnnonceRequest $request, Annonce $annonce): RedirectResponse
    {
        $this->authorize('update', $annonce);

        DB::transaction(function () use ($request, $annonce) {
            $data = [
                'titre' => $request->input('titre'),
                'description' => $request->input('description'),
                'prix_unitaire' => $request->input('prix_unitaire'),
                'quantite' => $request->input('quantite'),
                'poids' => $request->input('poids'),
                'mois' => $request->input('mois'),
                'unite_de_mesure' => $request->input('unite_de_mesure'),
                'etat' => $request->input('etat'),
                // Toute modification substantielle repasse par la modération.
                'statut' => Annonce::STATUT_EN_ATTENTE,
                'motif_rejet' => null,
            ];

            if ($request->hasFile('image_1')) {
                if ($annonce->image_1) {
                    Storage::disk('public')->delete($annonce->image_1);
                }
                $data['image_1'] = $this->enregistrerImage($request, 'image_1');
            }

            if ($request->hasFile('image_2')) {
                if ($annonce->image_2) {
                    Storage::disk('public')->delete($annonce->image_2);
                }
                $data['image_2'] = $this->enregistrerImage($request, 'image_2');
            }

            $annonce->update($data);

            $this->synchroniserReductions($annonce, $request->input('reductions', []));
        });

        return redirect()->route('mon-espace.annonces.index')
            ->with('status', "Votre annonce a été mise à jour et repasse en attente de validation.");
    }

    public function destroy(Annonce $annonce): RedirectResponse
    {
        $this->authorize('delete', $annonce);

        if ($annonce->commandes()->exists()) {
            return back()->withErrors([
                'annonce' => "Impossible de supprimer cette annonce : elle est déjà associée à au moins une commande.",
            ]);
        }

        if ($annonce->image_1) {
            Storage::disk('public')->delete($annonce->image_1);
        }
        if ($annonce->image_2) {
            Storage::disk('public')->delete($annonce->image_2);
        }

        $annonce->delete();

        return redirect()->route('mon-espace.annonces.index')->with('status', 'Annonce supprimée.');
    }

    /**
     * Enregistre un fichier image sur le disque public et retourne son
     * chemin. Toute erreur de bas niveau (droits d'écriture, disque plein,
     * lien symbolique storage:link manquant...) est convertie en erreur de
     * validation lisible plutôt que de remonter en erreur 500 générique —
     * c'est ce message précis qui doit maintenant apparaître à l'écran au
     * lieu d'un échec silencieux.
     */
    private function enregistrerImage(Request $request, string $champ): string
    {
        try {
            $chemin = $request->file($champ)->store('annonces', 'public');
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                $champ => "L'image n'a pas pu être enregistrée sur le serveur ({$e->getMessage()}). "
                    ."Vérifiez que le dossier storage/app/public est accessible en écriture "
                    ."et que la commande `php artisan storage:link` a bien été exécutée.",
            ]);
        }

        if ($chemin === false) {
            throw ValidationException::withMessages([
                $champ => "L'image n'a pas pu être enregistrée sur le serveur. Réessayez avec un fichier plus léger (max 4 Mo).",
            ]);
        }

        return $chemin;
    }

    /** Remplace intégralement le barème de réduction par quantité de l'annonce. */
    private function synchroniserReductions(Annonce $annonce, array $reductions): void
    {
        $annonce->reductions()->delete();

        foreach ($reductions as $reduction) {
            if (blank($reduction['quantite_min'] ?? null)) {
                continue;
            }

            $annonce->reductions()->create([
                'quantite_min' => $reduction['quantite_min'],
                'quantite_max' => $reduction['quantite_max'],
                'pourcentage_reduction' => $reduction['pourcentage_reduction'],
            ]);
        }
    }
}
