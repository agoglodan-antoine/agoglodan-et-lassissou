<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Crée 4 utilisateurs pour chacun des 7 rôles (28 comptes au total).
 * Mot de passe commun de test : "password".
 *
 * La géolocalisation (latitude/longitude) est désormais capturée une seule
 * fois ici, sur UTILISATEURS, et n'est plus dupliquée dans les 7 tables de
 * profil (ELEVEURS, ACHETEURS, VENDEUR_PROVENDE, VENDEUR_ACCESSOIRE,
 * VETERINAIRES, LIVREURS, ADMINISTRATEURS).
 */
class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'eleveur', 'acheteur', 'vendeur_provende', 'vendeur_accessoire',
            'veterinaire', 'livreur', 'administrateur',
        ];

        // Ville => [nom, latitude, longitude]
        $villes = [
            ['Parakou', 9.3370, 2.6303],
            ['Cotonou', 6.3703, 2.3912],
            ['Porto-Novo', 6.4969, 2.6036],
            ['Abomey', 7.1833, 1.9917],
        ];

        foreach ($roles as $role) {
            for ($i = 1; $i <= 4; $i++) {
                [$ville, $latitude, $longitude] = $villes[($i - 1) % 4];

                DB::table('utilisateurs')->insert([
                    'nom' => ucfirst($role) . 'Nom' . $i,
                    'prenom' => ucfirst($role) . 'Prenom' . $i,
                    'email' => strtolower($role) . $i . '@gmail.com',
                    'password' => Hash::make('password'),
                    'telephone' => '+229' . rand(60000000, 69999999),
                    'adresse' => $ville . ', Bénin',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'role' => $role,
                    'statut' => 'actif',
                    'date_inscription' => now(),
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
