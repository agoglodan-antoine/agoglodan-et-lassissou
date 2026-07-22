<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Compte commun à tous les acteurs de la plateforme (UTILISATEURS).
 * Un utilisateur possède un et un seul rôle (règle de gestion, chap. 3).
 */
class Utilisateur extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'utilisateurs';
    protected $primaryKey = 'id_utilisateur';

    const ROLE_ELEVEUR = 'eleveur';
    const ROLE_ACHETEUR = 'acheteur';
    const ROLE_VENDEUR_PROVENDE = 'vendeur_provende';
    const ROLE_VENDEUR_ACCESSOIRE = 'vendeur_accessoire';
    const ROLE_VETERINAIRE = 'veterinaire';
    const ROLE_LIVREUR = 'livreur';
    const ROLE_ADMINISTRATEUR = 'administrateur';

    /** Rôles pouvant publier une annonce dans le catalogue unifié. */
    const ROLES_FOURNISSEURS = [self::ROLE_ELEVEUR, self::ROLE_VENDEUR_PROVENDE, self::ROLE_VENDEUR_ACCESSOIRE];

    protected $fillable = [
        'nom', 'prenom', 'email', 'password', 'telephone', 'adresse', 'photo_profil',
        'latitude', 'longitude', 'role', 'statut',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_inscription' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- Profil spécifique au rôle (héritage Merise -> relations 1-1) ---
    public function eleveur() { return $this->hasOne(Eleveur::class, 'id_utilisateur'); }
    public function acheteur() { return $this->hasOne(Acheteur::class, 'id_utilisateur'); }
    public function vendeurProvende() { return $this->hasOne(VendeurProvende::class, 'id_utilisateur'); }
    public function vendeurAccessoire() { return $this->hasOne(VendeurAccessoire::class, 'id_utilisateur'); }
    public function veterinaire() { return $this->hasOne(Veterinaire::class, 'id_utilisateur'); }
    public function livreur() { return $this->hasOne(Livreur::class, 'id_utilisateur'); }
    public function administrateur() { return $this->hasOne(Administrateur::class, 'id_utilisateur'); }

    public function annonces() { return $this->hasMany(Annonce::class, 'id_utilisateur'); }
    public function commandes() { return $this->hasMany(Commande::class, 'id_acheteur'); }
    public function notifications_elevconnect() { return $this->hasMany(NotificationElevConnect::class, 'id_utilisateur'); }
    public function actualites() { return $this->hasMany(Actualite::class, 'id_auteur'); }

    /** Retourne le profil de rôle correspondant (relation dynamique unique). */
    public function profil()
    {
        return match ($this->role) {
            self::ROLE_ELEVEUR => $this->eleveur,
            self::ROLE_ACHETEUR => $this->acheteur,
            self::ROLE_VENDEUR_PROVENDE => $this->vendeurProvende,
            self::ROLE_VENDEUR_ACCESSOIRE => $this->vendeurAccessoire,
            self::ROLE_VETERINAIRE => $this->veterinaire,
            self::ROLE_LIVREUR => $this->livreur,
            self::ROLE_ADMINISTRATEUR => $this->administrateur,
            default => null,
        };
    }

    public function estFournisseur(): bool
    {
        return in_array($this->role, self::ROLES_FOURNISSEURS, true);
    }

    /** Règle de gestion : "une actualité peut être publiée par tout utilisateur, sauf l'acheteur". */
    public function peutPublierActualite(): bool
    {
        return $this->role !== self::ROLE_ACHETEUR;
    }
}
