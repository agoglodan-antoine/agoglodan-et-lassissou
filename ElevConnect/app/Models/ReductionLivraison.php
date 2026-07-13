<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Barème de réduction sur les frais de transport, défini par le livreur. */
class ReductionLivraison extends Model
{
    protected $table = 'reductions_livraison';
    protected $primaryKey = 'id_reduction';

    protected $fillable = ['id_livreur', 'quantite_min', 'quantite_max', 'pourcentage_reduction'];

    public function livreur() { return $this->belongsTo(Livreur::class, 'id_livreur', 'id_utilisateur'); }
}
