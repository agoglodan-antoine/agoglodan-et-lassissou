<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Barème de réduction par tranche de quantité, défini par le fournisseur. */
class ReductionAnnonce extends Model
{
    protected $table = 'reductions_annonce';
    protected $primaryKey = 'id_reduction';

    protected $fillable = ['id_annonce', 'quantite_min', 'quantite_max', 'pourcentage_reduction'];

    public function annonce() { return $this->belongsTo(Annonce::class, 'id_annonce'); }
}
