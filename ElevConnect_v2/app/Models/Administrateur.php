<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Profil Administrateur : modération, supervision globale de la plateforme. */
class Administrateur extends Model
{
    protected $table = 'administrateurs';
    protected $primaryKey = 'id_utilisateur';
    public $incrementing = false;

    protected $fillable = ['id_utilisateur'];

    public function utilisateur() { return $this->belongsTo(Utilisateur::class, 'id_utilisateur'); }
}
