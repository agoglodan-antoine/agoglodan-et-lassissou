<?php

namespace App\Policies;

use App\Models\Livraison;
use App\Models\Utilisateur;

/** Autorisations sur les livraisons (auto-découverte par Laravel). */
class LivraisonPolicy
{
    /** Accepter ou refuser une livraison qui lui a été spécifiquement assignée. */
    public function accepter(Utilisateur $utilisateur, Livraison $livraison): bool
    {
        return $utilisateur->id_utilisateur === $livraison->id_livreur
            && $livraison->statut === Livraison::STATUT_EN_ATTENTE;
    }

    public function gerer(Utilisateur $utilisateur, Livraison $livraison): bool
    {
        return $utilisateur->id_utilisateur === $livraison->id_livreur;
    }
}
