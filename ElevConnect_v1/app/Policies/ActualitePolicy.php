<?php

namespace App\Policies;

use App\Models\Actualite;
use App\Models\Utilisateur;

/** Autorisations sur les actualités (auto-découverte par Laravel). */
class ActualitePolicy
{
    public function create(Utilisateur $utilisateur): bool
    {
        return $utilisateur->peutPublierActualite();
    }

    public function update(Utilisateur $utilisateur, Actualite $actualite): bool
    {
        return $utilisateur->id_utilisateur === $actualite->id_auteur;
    }

    public function delete(Utilisateur $utilisateur, Actualite $actualite): bool
    {
        return $utilisateur->id_utilisateur === $actualite->id_auteur
            || $utilisateur->role === Utilisateur::ROLE_ADMINISTRATEUR;
    }
}
