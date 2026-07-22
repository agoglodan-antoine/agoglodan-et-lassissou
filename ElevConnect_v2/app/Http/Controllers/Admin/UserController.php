<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion des comptes utilisateurs par l'Administrateur : consultation,
 * filtrage par rôle, suspension / réactivation (UTILISATEURS.statut).
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $utilisateurs = Utilisateur::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->input('role')))
            ->when($request->filled('recherche'), function ($q) use ($request) {
                $terme = $request->input('recherche');
                $q->where(function ($sub) use ($terme) {
                    $sub->where('nom', 'like', "%{$terme}%")
                        ->orWhere('prenom', 'like', "%{$terme}%")
                        ->orWhere('email', 'like', "%{$terme}%");
                });
            })
            ->latest('date_inscription')
            ->paginate(20)
            ->withQueryString();

        return view('admin.utilisateurs.index', compact('utilisateurs'));
    }

    public function show(Request $request, Utilisateur $utilisateur): View
    {
        $profil = $utilisateur->profil();

        return view('admin.utilisateurs.show', compact('utilisateur', 'profil'));
    }

    public function suspendre(Request $request, Utilisateur $utilisateur): RedirectResponse
    {
        abort_if($utilisateur->role === Utilisateur::ROLE_ADMINISTRATEUR, 403,
            "Un administrateur ne peut pas suspendre un autre administrateur depuis cet écran.");

        $utilisateur->update(['statut' => 'suspendu']);

        return back()->with('status', "Compte de {$utilisateur->prenom} {$utilisateur->nom} suspendu.");
    }

    public function reactiver(Request $request, Utilisateur $utilisateur): RedirectResponse
    {
        $utilisateur->update(['statut' => 'actif']);

        return back()->with('status', "Compte de {$utilisateur->prenom} {$utilisateur->nom} réactivé.");
    }
}
