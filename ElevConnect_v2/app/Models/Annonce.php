<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catalogue unifié (animal / provende / accessoire).
 * type_annonce est déduit automatiquement du rôle de l'auteur à la publication.
 */
class Annonce extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'annonces';
    protected $primaryKey = 'id_annonce';

    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_VISIBLE = 'visible';
    const STATUT_REJETEE = 'rejetee';

    protected $fillable = [
        'id_utilisateur', 'type_annonce', 'titre', 'description', 'prix_unitaire', 'quantite',
        'poids', 'mois', 'unite_de_mesure', 'image_1', 'image_2', 'statut', 'motif_rejet',
        'etat', 'date_publication',
    ];

    protected function casts(): array
    {
        return [
            'prix_unitaire' => 'decimal:2',
            'date_publication' => 'datetime',
        ];
    }

    public function auteur() { return $this->belongsTo(Utilisateur::class, 'id_utilisateur'); }
    public function reductions() { return $this->hasMany(ReductionAnnonce::class, 'id_annonce'); }
    public function commandes() { return $this->hasMany(Commande::class, 'id_annonce'); }

    /** Déduit le type_annonce attendu à partir du rôle de l'auteur (miroir du trigger SQL). */
    public static function typeAttenduPourRole(string $role): ?string
    {
        return match ($role) {
            Utilisateur::ROLE_ELEVEUR => 'animal',
            Utilisateur::ROLE_VENDEUR_PROVENDE => 'provende',
            Utilisateur::ROLE_VENDEUR_ACCESSOIRE => 'accessoire',
            default => null,
        };
    }

    /** Retourne la tranche de réduction applicable à une quantité donnée, s'il y en a une. */
    public function reductionPourQuantite(int $quantite): ?ReductionAnnonce
    {
        return $this->reductions
            ->first(fn (ReductionAnnonce $r) => $quantite >= $r->quantite_min && $quantite <= $r->quantite_max);
    }

    /**
     * Calcule le détail de prix d'une commande pour une quantité donnée :
     * montant brut, réduction éventuelle, montant net (cf. COMMANDES.montant_net_commande).
     */
    public function calculerMontant(int $quantite): array
    {
        $montantTotal = round($this->prix_unitaire * $quantite, 2);
        $reduction = $this->reductionPourQuantite($quantite);
        $montantReduction = $reduction
            ? round($montantTotal * ((float) $reduction->pourcentage_reduction / 100), 2)
            : 0.0;

        return [
            'montant_total' => $montantTotal,
            'reduction_sur_commande' => $montantReduction,
            'montant_net_commande' => round($montantTotal - $montantReduction, 2),
        ];
    }
}
