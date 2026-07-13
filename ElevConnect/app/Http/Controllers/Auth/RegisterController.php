<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Acheteur;
use App\Models\Administrateur;
use App\Models\Eleveur;
use App\Models\Livreur;
use App\Models\Utilisateur;
use App\Models\VendeurAccessoire;
use App\Models\VendeurProvende;
use App\Models\Veterinaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Inscription différenciée par type d'acteur (exigence "Gestion des comptes", chap. 2).
 * Chaque compte enregistre une position GPS captée depuis le téléphone à l'inscription
 * (règle de gestion transversale, chap. 3) : les champs `latitude`/`longitude` sont
 * donc requis pour tous les rôles, y compris l'Administrateur.
 *
 * NOTE : L'inscription "administrateur" via ce formulaire public est volontairement
 * restreinte (cf. §register()) — dans une plateforme réelle, les comptes admin sont
 * provisionnés par un administrateur existant, pas via un formulaire public ouvert.
 */
class RegisterController extends Controller
{
    private const ROLES_PUBLICS = [
        Utilisateur::ROLE_ELEVEUR,
        Utilisateur::ROLE_ACHETEUR,
        Utilisateur::ROLE_VENDEUR_PROVENDE,
        Utilisateur::ROLE_VENDEUR_ACCESSOIRE,
        Utilisateur::ROLE_VETERINAIRE,
        Utilisateur::ROLE_LIVREUR,
    ];

    public function show(Request $request): View
    {
        $role = $request->query('role', Utilisateur::ROLE_ACHETEUR);

        if (! in_array($role, self::ROLES_PUBLICS, true)) {
            $role = Utilisateur::ROLE_ACHETEUR;
        }

        return view('auth.register', ['role' => $role, 'roles' => self::ROLES_PUBLICS]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(self::ROLES_PUBLICS)],
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:utilisateurs,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            // Champs spécifiques par rôle (tous optionnels au niveau validation générique ;
            // affinés par rôle dans resources/views/auth/register.blade.php côté front).
            'nom_exploitation' => ['nullable', 'string', 'max:150'],
            'nom_boutique' => ['nullable', 'string', 'max:150'],
            'specialite' => ['nullable', 'string', 'max:150'],
            'zone_intervention' => ['nullable', 'string', 'max:150'],
            'moyen_transport' => ['nullable', 'string', 'max:100'],
            'zone_couverture' => ['nullable', 'string', 'max:150'],
            'type_acheteur' => ['nullable', Rule::in(['particulier', 'professionnel'])],
        ]);

        $utilisateur = DB::transaction(function () use ($data) {
            $utilisateur = Utilisateur::create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'telephone' => $data['telephone'] ?? null,
                'adresse' => $data['adresse'] ?? null,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'role' => $data['role'],
                'statut' => 'actif',
                'date_inscription' => now(),
            ]);

            match ($data['role']) {
                Utilisateur::ROLE_ELEVEUR => Eleveur::create([
                    'id_utilisateur' => $utilisateur->id_utilisateur,
                    'nom_exploitation' => $data['nom_exploitation'] ?? null,
                ]),
                Utilisateur::ROLE_ACHETEUR => Acheteur::create([
                    'id_utilisateur' => $utilisateur->id_utilisateur,
                    'type_acheteur' => $data['type_acheteur'] ?? 'particulier',
                ]),
                Utilisateur::ROLE_VENDEUR_PROVENDE => VendeurProvende::create([
                    'id_utilisateur' => $utilisateur->id_utilisateur,
                    'nom_boutique' => $data['nom_boutique'] ?? null,
                ]),
                Utilisateur::ROLE_VENDEUR_ACCESSOIRE => VendeurAccessoire::create([
                    'id_utilisateur' => $utilisateur->id_utilisateur,
                    'nom_boutique' => $data['nom_boutique'] ?? null,
                ]),
                Utilisateur::ROLE_VETERINAIRE => Veterinaire::create([
                    'id_utilisateur' => $utilisateur->id_utilisateur,
                    'specialite' => $data['specialite'] ?? null,
                    'zone_intervention' => $data['zone_intervention'] ?? null,
                ]),
                Utilisateur::ROLE_LIVREUR => Livreur::create([
                    'id_utilisateur' => $utilisateur->id_utilisateur,
                    'moyen_transport' => $data['moyen_transport'] ?? null,
                    'zone_couverture' => $data['zone_couverture'] ?? null,
                ]),
                default => null,
            };

            return $utilisateur;
        });

        Auth::login($utilisateur);

        return redirect()->route('home')->with('status', 'Bienvenue sur ElevConnect, '.$utilisateur->prenom.' !');
    }
}
