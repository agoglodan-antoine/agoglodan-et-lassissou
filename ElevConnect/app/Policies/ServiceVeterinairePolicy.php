<?php

namespace App\Policies;

use App\Models\ServiceVeterinaire;
use App\Models\Utilisateur;

/** Autorisations sur les services vétérinaires (auto-découverte par Laravel). */
class ServiceVeterinairePolicy
{
    public function create(Utilisateur $utilisateur): bool
    {
        return $utilisateur->role === Utilisateur::ROLE_VETERINAIRE;
    }

    public function update(Utilisateur $utilisateur, ServiceVeterinaire $service): bool
    {
        return $utilisateur->id_utilisateur === $service->id_veterinaire;
    }

    public function delete(Utilisateur $utilisateur, ServiceVeterinaire $service): bool
    {
        return $utilisateur->id_utilisateur === $service->id_veterinaire;
    }
}
