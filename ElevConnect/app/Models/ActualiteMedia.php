<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Pièce jointe (image, vidéo, document) rattachée à une actualité. */
class ActualiteMedia extends Model
{
    protected $table = 'actualites_media';
    protected $primaryKey = 'id_media';

    protected $fillable = ['id_actualite', 'chemin_fichier', 'type_media'];

    public function actualite() { return $this->belongsTo(Actualite::class, 'id_actualite'); }
}
