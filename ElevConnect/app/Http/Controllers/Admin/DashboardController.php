<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\Commande;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Vue d'ensemble de l'espace Administrateur : indicateurs clés. */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user() && $request->user()->role === Utilisateur::ROLE_ADMINISTRATEUR,
            403
        );

        $stats = [
            'annonces_en_attente' => Annonce::where('statut', Annonce::STATUT_EN_ATTENTE)->count(),
            'litiges_ouverts' => Commande::where('statut', Commande::EN_LITIGE)->count(),
            'utilisateurs_actifs' => Utilisateur::where('statut', 'actif')->count(),
            'utilisateurs_suspendus' => Utilisateur::where('statut', 'suspendu')->count(),
            'commandes_confirmees' => Commande::where('statut', Commande::CONFIRMEE)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
