<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Ordre d'exécution respectant les clés étrangères et les triggers métier
 * (ex: trg_annonces_type_role, trg_commandes_pas_auto_commande, trg_actualites_auteur_role).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UtilisateurSeeder::class,
            EleveurSeeder::class,
            AcheteurSeeder::class,
            VendeurProvendeSeeder::class,
            VendeurAccessoireSeeder::class,
            VeterinaireSeeder::class,
            LivreurSeeder::class,
            AdministrateurSeeder::class,
            AnnonceSeeder::class,
            ReductionAnnonceSeeder::class,
            CommandeSeeder::class,
            PaiementSeeder::class,
            LivraisonSeeder::class,
            PlanningLivreurSeeder::class,
            ReductionLivraisonSeeder::class,
            VersementSeeder::class,
            ServiceVeterinaireSeeder::class,
            RendezVousSeeder::class,
            AbonnementSeeder::class,
            NotificationSeeder::class,
            ActualiteSeeder::class,
            ActualiteMediaSeeder::class,
        ]);
    }
}
