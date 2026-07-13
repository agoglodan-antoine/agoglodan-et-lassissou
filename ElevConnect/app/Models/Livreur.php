<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Profil Livreur : transport sécurisé, planning de disponibilité. */
class Livreur extends Model
{
    protected $table = 'livreurs';
    protected $primaryKey = 'id_utilisateur';
    public $incrementing = false;

    protected $fillable = ['id_utilisateur', 'moyen_transport', 'zone_couverture'];

    public function utilisateur() { return $this->belongsTo(Utilisateur::class, 'id_utilisateur'); }
    public function livraisons() { return $this->hasMany(Livraison::class, 'id_livreur'); }
    public function planning() { return $this->hasMany(PlanningLivreur::class, 'id_livreur'); }
    public function reductions() { return $this->hasMany(ReductionLivraison::class, 'id_livreur'); }
}
