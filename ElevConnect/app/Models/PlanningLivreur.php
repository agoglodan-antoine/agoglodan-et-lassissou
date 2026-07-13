<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Créneau de disponibilité personnelle du livreur. */
class PlanningLivreur extends Model
{
    protected $table = 'planning_livreur';
    protected $primaryKey = 'id_planning';

    protected $fillable = ['id_livreur', 'date_debut', 'date_fin', 'disponible'];

    protected function casts(): array
    {
        return ['date_debut' => 'datetime', 'date_fin' => 'datetime', 'disponible' => 'boolean'];
    }

    public function livreur() { return $this->belongsTo(Livreur::class, 'id_livreur', 'id_utilisateur'); }
}
