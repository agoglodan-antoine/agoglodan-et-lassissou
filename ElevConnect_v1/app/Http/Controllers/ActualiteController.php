<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use App\Models\ActualiteMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Actualités : publiables par tout rôle sauf l'Acheteur (règle de gestion —
 * cf. Utilisateur::peutPublierActualite() et trg_actualites_auteur_role dans
 * le schéma SQL). Aucune modération n'est requise par le cahier des charges
 * pour ce module (contrairement aux annonces) : publication directe.
 */
class ActualiteController extends Controller
{
    public function index(Request $request): View
    {
        $actualites = Actualite::with('auteur', 'medias')
            ->latest('date_publication')
            ->paginate(9);

        return view('actualites.index', compact('actualites'));
    }

    public function show(Actualite $actualite): View
    {
        $actualite->load('auteur', 'medias');

        return view('actualites.show', compact('actualite'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Actualite::class);

        return view('actualites.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Actualite::class);

        $data = $request->validate([
            'titre' => ['required', 'string', 'max:200'],
            'contenu' => ['required', 'string', 'max:5000'],
            'medias.*' => ['nullable', 'image', 'max:4096'],
        ]);

        $actualite = Actualite::create([
            'id_auteur' => $request->user()->id_utilisateur,
            'titre' => $data['titre'],
            'contenu' => $data['contenu'],
            'date_publication' => now(),
        ]);

        foreach ($request->file('medias', []) as $file) {
            ActualiteMedia::create([
                'id_actualite' => $actualite->id_actualite,
                'chemin_fichier' => $file->store('actualites', 'public'),
                'type_media' => 'image',
            ]);
        }

        return redirect()->route('actualites.show', $actualite)->with('status', 'Actualité publiée.');
    }

    public function edit(Actualite $actualite): View
    {
        $this->authorize('update', $actualite);

        return view('actualites.edit', compact('actualite'));
    }

    public function update(Request $request, Actualite $actualite): RedirectResponse
    {
        $this->authorize('update', $actualite);

        $data = $request->validate([
            'titre' => ['required', 'string', 'max:200'],
            'contenu' => ['required', 'string', 'max:5000'],
        ]);

        $actualite->update($data);

        return redirect()->route('actualites.show', $actualite)->with('status', 'Actualité mise à jour.');
    }

    public function destroy(Actualite $actualite): RedirectResponse
    {
        $this->authorize('delete', $actualite);

        foreach ($actualite->medias as $media) {
            Storage::disk('public')->delete($media->chemin_fichier);
        }

        $actualite->delete();

        return redirect()->route('actualites.index')->with('status', 'Actualité supprimée.');
    }
}
