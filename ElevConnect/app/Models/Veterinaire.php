<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Profil Vétérinaire : services, rendez-vous, seul rôle éligible à un abonnement. */
class Veterinaire extends Model
{
    protected $table = 'veterinaires';
    protected $primaryKey = 'id_utilisateur';
    public $incrementing = false;

    protected $fillable = [
        'id_utilisateur', 'specialite', 'zone_intervention',
        'certificat_chemin', 'note_moyenne', 'nombre_avis',
    ];

    public function utilisateur() { return $this->belongsTo(Utilisateur::class, 'id_utilisateur'); }
    public function services() { return $this->hasMany(ServiceVeterinaire::class, 'id_veterinaire'); }
    public function rendezVous() { return $this->hasMany(RendezVous::class, 'id_veterinaire'); }
    public function abonnements() { return $this->hasMany(Abonnement::class, 'id_veterinaire'); }

    public function abonnementActif()
    {
        return $this->hasOne(Abonnement::class, 'id_veterinaire')
            ->where('statut', 'actif')
            ->latest('date_expiration');
    }

    /** Vrai si le vétérinaire a un abonnement Premium actif et non expiré. */
    public function estPremium(): bool
    {
        $abonnement = $this->abonnementActif;

        return $abonnement !== null
            && $abonnement->formule === Abonnement::PREMIUM
            && ! $abonnement->date_expiration->isPast();
    }

    /** Nombre maximal de services publiables en formule Basique (voir config('elevconnect')). */
    public function limiteServicesAtteinte(): bool
    {
        if ($this->estPremium()) {
            return false;
        }

        $limite = config('elevconnect.abonnement_veterinaire.basique.limite_services', 3);

        return $this->services()->count() >= $limite;
    }
}
