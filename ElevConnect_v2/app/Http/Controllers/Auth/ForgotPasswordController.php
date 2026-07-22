<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * Réinitialisation de mot de passe oublié (exigence "Gestion des comptes",
 * chap. 2, et cas de test explicite du chap. 5 : "lien de réinitialisation
 * reçu par email"). Utilise le broker de mots de passe natif de Laravel
 * (Illuminate\Support\Facades\Password) — voir README_ROADMAP.md pour la
 * configuration requise de config/auth.php (provider pointant vers
 * App\Models\Utilisateur) et du mailer.
 */
class ForgotPasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        // Le message reste identique que l'adresse existe ou non, pour ne
        // pas révéler si un compte est associé à cet email (seul un
        // dépassement du quota d'envoi est signalé distinctement).
        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors(['email' => "Trop de tentatives. Réessayez dans quelques minutes."]);
        }

        return back()->with('status', "Si cette adresse existe, un lien de réinitialisation vient de lui être envoyé.");
    }
}
