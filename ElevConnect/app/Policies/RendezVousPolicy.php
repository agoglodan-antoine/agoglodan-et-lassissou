<?php

namespace App\Policies;

use App\Models\RendezVous;
use App\Models\Utilisateur;

/** Autorisations sur les rendez-vous vétérinaires (auto-découverte par Laravel). */
class RendezVousPolicy
{
    public function create(Utilisateur $utilisateur): bool
    {
        return $utilisateur->role === Utilisateur::ROLE_ELEVEUR;
    }

    /** L'éleveur consulte/annule son propre rendez-vous. */
    public function view(Utilisateur $utilisateur, RendezVous $rdv): bool
    {
        return $utilisateur->id_utilisateur === $rdv->id_eleveur
            || $utilisateur->id_utilisateur === $rdv->id_veterinaire;
    }

    public function annuler(Utilisateur $utilisateur, RendezVous $rdv): bool
    {
        return $utilisateur->id_utilisateur === $rdv->id_eleveur
            && in_array($rdv->statut, [RendezVous::EN_ATTENTE, RendezVous::CONFIRME], true);
    }

    /** Le vétérinaire concerné traite la demande (confirme, refuse, marque réalisé). */
    public function traiter(Utilisateur $utilisateur, RendezVous $rdv): bool
    {
        return $utilisateur->id_utilisateur === $rdv->id_veterinaire;
    }
}
