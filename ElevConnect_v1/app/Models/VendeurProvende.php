<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Profil Vendeur de provende et nourriture d'élevage. */
class VendeurProvende extends Model
{
    protected $table = 'vendeur_provende';
    protected $primaryKey = 'id_utilisateur';
    public $incrementing = false;

    protected $fillable = [
        'id_utilisateur', 'nom_boutique', 'certificat_chemin',
        'note_moyenne', 'nombre_avis',
    ];

    public function utilisateur() { return $this->belongsTo(Utilisateur::class, 'id_utilisateur'); }
}
