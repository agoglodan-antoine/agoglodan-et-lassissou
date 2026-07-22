<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Paiement en séquestre associé à une commande (relation 1-1), jusqu'au reversement.
 * Le taux de commission (5%) est centralisé dans config('elevconnect.commission_rate').
 */
class Paiement extends Model
{
    protected $table = 'paiements';
    protected $primaryKey = 'id_paiement';

    protected $fillable = [
        'id_commande', 'montant_net_commande', 'montant_net_livraison', 'total_general',
        'moyen_de_paiement', 'numero_de_compte', 'commission_sur_commande',
        'commission_sur_livraison', 'total_commission', 'montant_a_verser_au_fournisseur',
        'montant_a_verser_au_livreur', 'statut_paiement', 'date_paiement',
    ];

    protected function casts(): array
    {
        return ['date_paiement' => 'datetime'];
    }

    public function commande() { return $this->belongsTo(Commande::class, 'id_commande'); }
    public function versements() { return $this->hasMany(Versement::class, 'id_paiement'); }

    /**
     * Calcule la répartition commission / montant à verser, selon la règle de gestion :
     * "ElevConnect prélève une commission de 5% sur le montant net de chaque commande
     * et de chaque livraison" (mémoire, chap. 3).
     */
    public static function calculerCommission(float $montantNet): array
    {
        $taux = config('elevconnect.commission_rate', 0.05);
        $commission = round($montantNet * $taux, 2);

        return [
            'commission' => $commission,
            'montant_a_verser' => round($montantNet - $commission, 2),
        ];
    }
}
