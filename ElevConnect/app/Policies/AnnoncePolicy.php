<?php

namespace App\Policies;

use App\Models\Annonce;
use App\Models\Utilisateur;

/**
 * Autorisations sur les annonces.
 * Convention Laravel : cette classe est auto-découverte pour le modèle Annonce
 * (App\Policies\AnnoncePolicy <-> App\Models\Annonce), sans registre explicite.
 */
class AnnoncePolicy
{
    public function create(Utilisateur $utilisateur): bool
    {
        return $utilisateur->estFournisseur();
    }

    public function update(Utilisateur $utilisateur, Annonce $annonce): bool
    {
        return $utilisateur->id_utilisateur === $annonce->id_utilisateur;
    }

    public function delete(Utilisateur $utilisateur, Annonce $annonce): bool
    {
        return $utilisateur->id_utilisateur === $annonce->id_utilisateur;
    }

    /** Seul un administrateur peut approuver/rejeter une annonce en attente. */
    public function moderer(Utilisateur $utilisateur, Annonce $annonce): bool
    {
        return $utilisateur->role === Utilisateur::ROLE_ADMINISTRATEUR;
    }
}
