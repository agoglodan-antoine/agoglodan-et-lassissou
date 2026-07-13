<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Service et tarif proposés par un vétérinaire, consultable avant prise de rendez-vous. */
class ServiceVeterinaire extends Model
{
    protected $table = 'services_veterinaires';
    protected $primaryKey = 'id_service';

    protected $fillable = [
        'id_veterinaire', 'titre_service', 'description', 'prix', 'temps_traitement',
        'photo_illustrative', 'statut_service',
    ];

    public function veterinaire() { return $this->belongsTo(Veterinaire::class, 'id_veterinaire', 'id_utilisateur'); }
    public function rendezVous() { return $this->hasMany(RendezVous::class, 'id_service'); }
}
