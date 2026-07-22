<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestion de profil (exigence fonctionnelle explicite, chap. 2 : "gestion
 * de profil", pour tous les rôles). Permet de modifier les informations
 * communes (UTILISATEURS) ainsi que les attributs propres au rôle, et de
 * changer son mot de passe séparément.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', ['user' => $user, 'profil' => $user->profil()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('utilisateurs', 'email')->ignore($user->id_utilisateur, 'id_utilisateur')],
            'telephone' => ['nullable', 'string', 'max:20'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Champs propres au rôle (tous facultatifs ici ; seuls ceux du
            // rôle courant sont affichés et exploités côté vue).
            'nom_exploitation' => ['nullable', 'string', 'max:150'],
            'nom_boutique' => ['nullable', 'string', 'max:150'],
            'specialite' => ['nullable', 'string', 'max:150'],
            'zone_intervention' => ['nullable', 'string', 'max:150'],
            'moyen_transport' => ['nullable', 'string', 'max:100'],
            'zone_couverture' => ['nullable', 'string', 'max:150'],
            'type_acheteur' => ['nullable', Rule::in(['particulier', 'professionnel'])],
        ]);

        $user->update([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'telephone' => $data['telephone'] ?? null,
            'adresse' => $data['adresse'] ?? null,
            'latitude' => $data['latitude'] ?? $user->latitude,
            'longitude' => $data['longitude'] ?? $user->longitude,
        ]);

        // Chaque modèle de profil (Eleveur, VendeurProvende, Veterinaire...) ne
        // déclare dans son $fillable que ses propres colonnes : Eloquent ignore
        // silencieusement les clés qui ne s'appliquent pas au rôle courant, pas
        // besoin de filtrage manuel ici.
        $profil = $user->profil();
        if ($profil) {
            $profil->update([
                'nom_exploitation' => $data['nom_exploitation'] ?? null,
                'nom_boutique' => $data['nom_boutique'] ?? null,
                'specialite' => $data['specialite'] ?? null,
                'zone_intervention' => $data['zone_intervention'] ?? null,
                'moyen_transport' => $data['moyen_transport'] ?? null,
                'zone_couverture' => $data['zone_couverture'] ?? null,
                'type_acheteur' => $data['type_acheteur'] ?? null,
            ]);
        }

        return back()->with('status', 'Profil mis à jour.');
    }

    /** Changement de mot de passe, séparé du reste du profil par prudence. */
    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password_actuel' => ['required', 'current_password'],
            'password' => \App\Http\Controllers\Auth\ResetPasswordController::REGLE_MOT_DE_PASSE,
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'Mot de passe modifié.');
    }
}
