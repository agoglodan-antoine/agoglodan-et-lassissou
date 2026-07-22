<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rendez-vous de consultation vétérinaire.
 * Paiement effectué hors plateforme, directement entre l'éleveur et le vétérinaire.
 */
class RendezVous extends Model
{
    protected $table = 'rendez_vous';
    protected $primaryKey = 'id_rdv';

    const EN_ATTENTE = 'en_attente';
    const CONFIRME = 'confirme';
    const REALISE = 'realise';
    const ANNULE = 'annule';
    const REFUSE = 'refuse';

    protected $fillable = [
        'id_eleveur', 'id_veterinaire', 'id_service', 'sujet', 'description', 'date_prevue',
        'statut', 'image_1', 'image_2', 'video', 'note_client', 'avis_client', 'date_creation',
    ];

    protected function casts(): array
    {
        return ['date_prevue' => 'datetime', 'date_creation' => 'datetime'];
    }

    public function eleveur() { return $this->belongsTo(Eleveur::class, 'id_eleveur', 'id_utilisateur'); }
    public function veterinaire() { return $this->belongsTo(Veterinaire::class, 'id_veterinaire', 'id_utilisateur'); }
    public function service() { return $this->belongsTo(ServiceVeterinaire::class, 'id_service'); }
}
