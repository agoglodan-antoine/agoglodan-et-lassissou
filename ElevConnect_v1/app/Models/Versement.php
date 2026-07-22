<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Reversement effectué par ElevConnect au fournisseur et/ou au livreur. */
class Versement extends Model
{
    protected $table = 'versements';
    protected $primaryKey = 'id_versement';

    const BENEFICIAIRE_FOURNISSEUR = 'fournisseur';
    const BENEFICIAIRE_LIVREUR = 'livreur';

    protected $fillable = [
        'id_commande', 'id_paiement', 'type_beneficiaire', 'id_beneficiaire',
        'montant_verser', 'moyen_de_versement', 'numero_de_compte', 'statut_versement',
        'date_versement',
    ];

    protected function casts(): array
    {
        return ['date_versement' => 'datetime'];
    }

    public function commande() { return $this->belongsTo(Commande::class, 'id_commande'); }
    public function paiement() { return $this->belongsTo(Paiement::class, 'id_paiement'); }
    public function beneficiaire() { return $this->belongsTo(Utilisateur::class, 'id_beneficiaire'); }
}
