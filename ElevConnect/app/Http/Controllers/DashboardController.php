<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Livraison;
use App\Models\RendezVous;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Tableau de bord personnel — point d'entrée unique après connexion, quel
 * que soit le rôle. Chaque rôle voit des indicateurs et des raccourcis
 * adaptés à son activité ; il n'est plus redirigé directement vers un seul
 * module (voir remarque utilisateur : "il faut un vrai dashboard").
 * L'Administrateur est redirigé vers sa propre vue d'ensemble dédiée
 * (Admin\DashboardController), plus riche et réservée à ce rôle.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->role === Utilisateur::ROLE_ADMINISTRATEUR) {
            return redirect()->route('admin.dashboard');
        }

        $stats = [];

        if ($user->estFournisseur()) {
            $stats['annonces_total'] = $user->annonces()->count();
            $stats['annonces_visibles'] = $user->annonces()->where('statut', 'visible')->count();
            $stats['annonces_en_attente'] = $user->annonces()->where('statut', 'en_attente')->count();
            $stats['commandes_a_traiter'] = Commande::whereHas('annonce', fn ($q) => $q->where('id_utilisateur', $user->id_utilisateur))
                ->whereIn('statut', ['payee', 'en_cours_de_traitement'])
                ->count();
        }

        // Tout rôle peut acheter (voir CommandePolicy::create) : ces indicateurs
        // sont donc affichés pour tout le monde, pas seulement pour l'Acheteur.
        $stats['mes_commandes_en_cours'] = $user->commandes()
            ->whereNotIn('statut', ['confirmee', 'annulee', 'refusee'])
            ->count();
        $stats['mes_commandes_total'] = $user->commandes()->count();

        if ($user->role === Utilisateur::ROLE_LIVREUR && $user->livreur) {
            $stats['livraisons_proposees'] = $user->livreur->livraisons()
                ->where('statut', Livraison::STATUT_EN_ATTENTE)
                ->count();
            $stats['mes_livraisons_en_cours'] = $user->livreur->livraisons()
                ->whereIn('statut', ['prise_en_charge', 'en_cours'])
                ->count();
        }

        if ($user->role === Utilisateur::ROLE_VETERINAIRE && $user->veterinaire) {
            $stats['services_total'] = $user->veterinaire->services()->count();
            $stats['rdv_en_attente'] = $user->veterinaire->rendezVous()->where('statut', RendezVous::EN_ATTENTE)->count();
            $stats['abonnement_formule'] = $user->veterinaire->estPremium() ? 'Premium' : 'Basique';
        }

        if ($user->role === Utilisateur::ROLE_ELEVEUR && $user->eleveur) {
            $stats['mes_rdv_a_venir'] = $user->eleveur->rendezVous()
                ->whereIn('statut', [RendezVous::EN_ATTENTE, RendezVous::CONFIRME])
                ->count();
        }

        $stats['notifications_non_lues'] = $user->notifications_elevconnect()->where('lu', false)->count();

        return view('dashboard.index', compact('stats', 'user'));
    }
}
