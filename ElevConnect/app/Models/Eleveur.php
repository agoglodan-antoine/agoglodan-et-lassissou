<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Profil Éleveur : gère son cheptel, publie des annonces d'animaux. */
class Eleveur extends Model
{
    protected $table = 'eleveurs';
    protected $primaryKey = 'id_utilisateur';
    public $incrementing = false;

    protected $fillable = [
        'id_utilisateur', 'nom_exploitation', 'certificat_chemin',
        'note_moyenne', 'nombre_avis',
    ];

    public function utilisateur() { return $this->belongsTo(Utilisateur::class, 'id_utilisateur'); }
    public function rendezVous() { return $this->hasMany(RendezVous::class, 'id_eleveur'); }
}
