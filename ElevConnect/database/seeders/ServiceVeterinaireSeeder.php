<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceVeterinaireSeeder extends Seeder
{
    public function run(): void
    {
        $veterinaires = DB::table('veterinaires')->pluck('id_utilisateur')->values();

        $services = [
            ['titre_service' => 'Consultation générale', 'prix' => 10000, 'temps_traitement' => 30],
            ['titre_service' => 'Vaccination bovine', 'prix' => 7500, 'temps_traitement' => 20],
            ['titre_service' => 'Déparasitage volaille', 'prix' => 5000, 'temps_traitement' => 15],
            ['titre_service' => 'Chirurgie mineure', 'prix' => 25000, 'temps_traitement' => 60],
        ];

        foreach ($veterinaires as $i => $id) {
            $service = $services[$i % count($services)];
            DB::table('services_veterinaires')->insert([
                'id_veterinaire' => $id,
                'titre_service' => $service['titre_service'],
                'description' => 'Service proposé par le vétérinaire, sur rendez-vous.',
                'prix' => $service['prix'],
                'temps_traitement' => $service['temps_traitement'],
                'photo_illustrative' => 'services/service_' . ($i + 1) . '.jpg',
                'statut_service' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
