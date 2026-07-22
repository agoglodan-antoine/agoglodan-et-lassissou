<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Connexion sécurisée (email + mot de passe haché — exigence "Gestion des comptes").
 * Structure initialement générée par le starter kit laravel/breeze (voir
 * composer.json, require-dev), puis entièrement réécrite pour coller au
 * schéma de rôles ElevConnect (7 profils) et à la maquette du site plutôt
 * qu'au design par défaut de Breeze.
 */
class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => "Identifiants incorrects."])->onlyInput('email');
        }

        $user = Auth::user();

        if ($user->statut === 'suspendu') {
            Auth::logout();
            return back()->withErrors(['email' => "Ce compte a été suspendu par un administrateur."]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
