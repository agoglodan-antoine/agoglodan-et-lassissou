<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Profil Vendeur d'accessoires et outils d'élevage. */
class VendeurAccessoire extends Model
{
    protected $table = 'vendeur_accessoire';
    protected $primaryKey = 'id_utilisateur';
    public $incrementing = false;

    protected $fillable = [
        'id_utilisateur', 'nom_boutique', 'certificat_chemin',
        'note_moyenne', 'nombre_avis',
    ];

    public function utilisateur() { return $this->belongsTo(Utilisateur::class, 'id_utilisateur'); }
}
