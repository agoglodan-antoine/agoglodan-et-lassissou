<?php

namespace App\Policies;

use App\Models\Livraison;
use App\Models\Utilisateur;

/** Autorisations sur les livraisons (auto-découverte par Laravel). */
class LivraisonPolicy
{
    /** Le livreur consulte le détail d'une livraison qui lui est (ou a été) assignée. */
    public function view(Utilisateur $utilisateur, Livraison $livraison): bool
    {
        return $utilisateur->id_utilisateur === $livraison->id_livreur;
    }

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
