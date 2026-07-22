<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\Commande;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Vue d'ensemble de l'espace Administrateur : indicateurs clés et graphiques. */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'annonces_en_attente' => Annonce::where('statut', Annonce::STATUT_EN_ATTENTE)->count(),
            'annonces_visibles' => Annonce::where('statut', Annonce::STATUT_VISIBLE)->count(),
            'annonces_rejetees' => Annonce::where('statut', Annonce::STATUT_REJETEE)->count(),
            'utilisateurs_actifs' => Utilisateur::where('statut', 'actif')->count(),
            'utilisateurs_suspendus' => Utilisateur::where('statut', 'suspendu')->count(),
            'commandes_confirmees' => Commande::where('statut', Commande::CONFIRMEE)->count(),
            'volume_affaires' => (float) Commande::where('statut', Commande::CONFIRMEE)->sum('montant_net_commande'),
        ];

        $repartitionRoles = Utilisateur::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $libellesRoles = [
            'eleveur' => 'Éleveurs', 'acheteur' => 'Acheteurs',
            'vendeur_provende' => 'Vendeurs provende', 'vendeur_accessoire' => "Vendeurs accessoires",
            'veterinaire' => 'Vétérinaires', 'livreur' => 'Livreurs', 'administrateur' => 'Administrateurs',
        ];

        $graphiques = [
            'nouveaux_utilisateurs' => $this->serieMensuelle(fn () => Utilisateur::query(), 'date_inscription'),
            'commandes' => $this->serieMensuelle(fn () => Commande::query(), 'date_commande'),
            'utilisateurs_par_role' => [
                'labels' => $repartitionRoles->keys()->map(fn ($role) => $libellesRoles[$role] ?? $role)->values()->all(),
                'valeurs' => $repartitionRoles->values()->all(),
            ],
            'annonces_par_statut' => [
                'labels' => ['Visibles', 'En attente', 'Rejetées'],
                'valeurs' => [$stats['annonces_visibles'], $stats['annonces_en_attente'], $stats['annonces_rejetees']],
            ],
        ];

        return view('admin.dashboard', compact('stats', 'graphiques'));
    }

    /** Construit une série "6 derniers mois" (labels + valeurs) — voir DashboardController. */
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
