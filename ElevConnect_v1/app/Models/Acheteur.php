<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Profil Acheteur : recherche, commande et règle en ligne. */
class Acheteur extends Model
{
    protected $table = 'acheteurs';
    protected $primaryKey = 'id_utilisateur';
    public $incrementing = false;

    protected $fillable = ['id_utilisateur', 'type_acheteur'];

    public function utilisateur() { return $this->belongsTo(Utilisateur::class, 'id_utilisateur'); }
}
