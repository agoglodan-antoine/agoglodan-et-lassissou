<?php

namespace App\Policies;

use App\Models\Commande;
use App\Models\Utilisateur;

/** Autorisations sur les commandes (auto-découverte par Laravel). */
class CommandePolicy
{
    /**
     * Tout utilisateur connecté peut passer commande — y compris un Éleveur,
     * un Vendeur ou un Vétérinaire qui souhaite acheter de la provende, des
     * accessoires ou un animal. Seule exception : ne pas pouvoir commander
     * sa propre annonce, vérifié séparément dans CommandeController
     * (id_utilisateur de l'annonce vs utilisateur connecté).
     */
    public function create(Utilisateur $utilisateur): bool
    {
        return true;
    }

    /** L'acheteur consulte/paie/annule sa propre commande. */
    public function view(Utilisateur $utilisateur, Commande $commande): bool
    {
        return $utilisateur->id_utilisateur === $commande->id_acheteur;
    }

    public function annuler(Utilisateur $utilisateur, Commande $commande): bool
    {
        return $utilisateur->id_utilisateur === $commande->id_acheteur
            && in_array($commande->statut, [Commande::EN_ATTENTE, Commande::PAYEE, Commande::EN_COURS_DE_TRAITEMENT], true);
    }

    /** Le fournisseur (propriétaire de l'annonce) gère le traitement de la commande. */
    public function traiter(Utilisateur $utilisateur, Commande $commande): bool
    {
        return $utilisateur->id_utilisateur === $commande->annonce->id_utilisateur;
    }
}
