<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Article de valorisation publié par tout rôle sauf Acheteur. */
class Actualite extends Model
{
    protected $table = 'actualites';
    protected $primaryKey = 'id_actualite';

    protected $fillable = ['id_auteur', 'titre', 'contenu', 'date_publication'];

    protected function casts(): array
    {
        return ['date_publication' => 'datetime'];
    }

    public function auteur() { return $this->belongsTo(Utilisateur::class, 'id_auteur'); }
    public function medias() { return $this->hasMany(ActualiteMedia::class, 'id_actualite'); }
}
