<?php

namespace App\Policies;

use App\Models\Livraison;
use App\Models\Utilisateur;

/** Autorisations sur les livraisons (auto-découverte par Laravel). */
class LivraisonPolicy
{
    public function accepter(Utilisateur $utilisateur, Livraison $livraison): bool
    {
        return $utilisateur->role === Utilisateur::ROLE_LIVREUR && $livraison->id_livreur === null;
    }

    public function gerer(Utilisateur $utilisateur, Livraison $livraison): bool
    {
        return $utilisateur->id_utilisateur === $livraison->id_livreur;
    }
}
