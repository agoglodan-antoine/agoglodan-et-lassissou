<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Models\Commande;
use App\Models\Livraison;
use App\Models\RendezVous;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Tableau de bord personnel — point d'entrée unique après connexion, quel
 * que soit le rôle. Chaque rôle voit des indicateurs, des raccourcis et des
 * graphiques d'activité adaptés à son métier, plutôt qu'une redirection
 * directe vers un seul module.
 * L'Administrateur est redirigé vers sa propre vue d'ensemble dédiée
 * (Admin\DashboardController), plus riche et réservée à ce rôle.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->role === Utilisateur::ROLE_ADMINISTRATEUR) {
            return redirect()->route('mon-espace.admin.dashboard');
        }

        $stats = [];

        if ($user->estFournisseur()) {
            $stats['annonces_total'] = $user->annonces()->count();
            $stats['annonces_visibles'] = $user->annonces()->where('statut', 'visible')->count();
            $stats['annonces_en_attente'] = $user->annonces()->where('statut', 'en_attente')->count();
            $stats['annonces_rejetees'] = $user->annonces()->where('statut', 'rejetee')->count();
            $stats['commandes_a_traiter'] = Commande::whereHas('annonce', fn ($q) => $q->where('id_utilisateur', $user->id_utilisateur))
                ->whereIn('statut', ['payee', 'en_cours_de_traitement'])
                ->count();
            $stats['commandes_confirmees'] = Commande::whereHas('annonce', fn ($q) => $q->where('id_utilisateur', $user->id_utilisateur))
                ->where('statut', Commande::CONFIRMEE)
                ->count();
            $stats['chiffre_affaires'] = (float) Commande::whereHas('annonce', fn ($q) => $q->where('id_utilisateur', $user->id_utilisateur))
                ->where('statut', Commande::CONFIRMEE)
                ->sum('montant_net_commande');
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
            $stats['mes_livraisons_terminees'] = $user->livreur->livraisons()
                ->where('statut', Livraison::STATUT_TERMINEE)
                ->count();
            $stats['revenus_livraison'] = (float) $user->livreur->livraisons()
                ->where('statut', Livraison::STATUT_TERMINEE)
                ->sum('montant_net_livraison');
        }

        if ($user->role === Utilisateur::ROLE_VETERINAIRE && $user->veterinaire) {
            $stats['services_total'] = $user->veterinaire->services()->count();
            $stats['rdv_en_attente'] = $user->veterinaire->rendezVous()->where('statut', RendezVous::EN_ATTENTE)->count();
            $stats['rdv_realises'] = $user->veterinaire->rendezVous()->where('statut', RendezVous::REALISE)->count();
            $stats['abonnement_formule'] = $user->veterinaire->estPremium() ? 'Premium' : 'Basique';
        }

        if ($user->role === Utilisateur::ROLE_ELEVEUR && $user->eleveur) {
            $stats['mes_rdv_a_venir'] = $user->eleveur->rendezVous()
                ->whereIn('statut', [RendezVous::EN_ATTENTE, RendezVous::CONFIRME])
                ->count();
        }

        $stats['notifications_non_lues'] = $user->notifications_elevconnect()->where('lu', false)->count();

        // --- Données de graphiques (6 derniers mois) ---
        // Chaque série reçoit une closure qui reconstruit une requête neuve
        // à chaque appel, plutôt qu'un builder à cloner : cloner une relation
        // Eloquent (HasMany...) ne clone pas nécessairement sa requête sous-
        // jacente (contrairement à Builder::__clone()), ce qui pourrait faire
        // fuiter des conditions d'une requête à l'autre.
        $graphiques = [
            'mes_achats' => $this->serieMensuelle(fn () => $user->commandes(), 'date_commande'),
        ];

        if ($user->estFournisseur()) {
            $graphiques['commandes_recues'] = $this->serieMensuelle(
                fn () => Commande::whereHas('annonce', fn ($q) => $q->where('id_utilisateur', $user->id_utilisateur)),
                'date_commande'
            );
            $graphiques['annonces_par_statut'] = [
                'labels' => ['Visibles', 'En attente', 'Rejetées'],
                'valeurs' => [$stats['annonces_visibles'], $stats['annonces_en_attente'], $stats['annonces_rejetees']],
            ];
        }

        if ($user->role === Utilisateur::ROLE_LIVREUR && $user->livreur) {
            $graphiques['mes_livraisons'] = $this->serieMensuelle(fn () => $user->livreur->livraisons(), 'created_at');
        }

        if ($user->role === Utilisateur::ROLE_VETERINAIRE && $user->veterinaire) {
            $graphiques['mes_rdv'] = $this->serieMensuelle(fn () => $user->veterinaire->rendezVous(), 'date_prevue');
        }

        return view('dashboard.index', compact('stats', 'user', 'graphiques'));
    }

    /**
     * Construit une série "6 derniers mois" (labels + valeurs) à partir
     * d'une closure reconstruisant la requête et d'une colonne date, pour
     * alimenter les graphiques Chart.js. Retourne un tableau PHP simple
     * (pas de Collection) — voir la note dans CommandeController::create()
     * sur la fragilité de @json() combiné à une expression chaînée
     * directement dans la vue : on prépare donc toujours les données ici,
     * jamais dans le Blade.
     */
    private function serieMensuelle(\Closure $requete, string $colonneDate): array
    {
        $debut = now()->subMonths(5)->startOfMonth();

        $lignes = $requete()
            ->where($colonneDate, '>=', $debut)
            ->selectRaw("DATE_FORMAT($colonneDate, '%Y-%m') as mois, COUNT(*) as total")
            ->groupBy('mois')
            ->pluck('total', 'mois');

        $labels = [];
        $valeurs = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $cle = $date->format('Y-m');
            $labels[] = ucfirst($date->translatedFormat('M Y'));
            $valeurs[] = (int) ($lignes[$cle] ?? 0);
        }

        return ['labels' => $labels, 'valeurs' => $valeurs];
    }
}
