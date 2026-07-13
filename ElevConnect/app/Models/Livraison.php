<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Livraison associée à une commande — intervention du livreur optionnelle. */
class Livraison extends Model
{
    protected $table = 'livraison';
    protected $primaryKey = 'id_livraison';

    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_PRISE_EN_CHARGE = 'prise_en_charge';
    const STATUT_REJETEE = 'rejetee';
    const STATUT_EN_COURS = 'en_cours';
    const STATUT_TERMINEE = 'terminee';

    protected $fillable = [
        'id_commande', 'id_livreur', 'adresse_fournisseur', 'adresse_client',
        'frais_de_livraison', 'reduction_sur_frais', 'montant_net_livraison', 'statut',
        'verification_authenticite', 'date_verification_qr', 'description',
        'note_client_livraison', 'avis_client_livraison',
    ];

    protected function casts(): array
    {
        return ['date_verification_qr' => 'datetime'];
    }

    public function commande() { return $this->belongsTo(Commande::class, 'id_commande'); }
    public function livreur() { return $this->belongsTo(Livreur::class, 'id_livreur', 'id_utilisateur'); }

    /**
     * Calcule le détail des frais de livraison pour une quantité de commande
     * donnée, selon le barème de réduction propre au livreur (REDUCTIONS_LIVRAISON).
     */
    public static function calculerFrais(Livreur $livreur, float $fraisBase, int $quantite): array
    {
        $reduction = $livreur->reductions
            ->first(fn (ReductionLivraison $r) => $quantite >= $r->quantite_min && $quantite <= $r->quantite_max);

        $montantReduction = $reduction
            ? round($fraisBase * ((float) $reduction->pourcentage_reduction / 100), 2)
            : 0.0;

        return [
            'frais_de_livraison' => $fraisBase,
            'reduction_sur_frais' => $montantReduction,
            'montant_net_livraison' => round($fraisBase - $montantReduction, 2),
        ];
    }
}
