<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Abonnement Basique/Premium souscrit par un vétérinaire (2000 FCFA/mois — Premium uniquement). */
class Abonnement extends Model
{
    protected $table = 'abonnements';
    protected $primaryKey = 'id_abonnement';

    const BASIQUE = 'basique';
    const PREMIUM = 'premium';

    protected $fillable = ['id_veterinaire', 'formule', 'date_debut', 'date_expiration', 'statut'];

    protected function casts(): array
    {
        return ['date_debut' => 'date', 'date_expiration' => 'date'];
    }

    public function veterinaire() { return $this->belongsTo(Veterinaire::class, 'id_veterinaire', 'id_utilisateur'); }

    public function estExpire(): bool
    {
        return $this->date_expiration->isPast();
    }
}
