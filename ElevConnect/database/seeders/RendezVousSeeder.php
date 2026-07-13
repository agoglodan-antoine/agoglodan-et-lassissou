<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RendezVousSeeder extends Seeder
{
    public function run(): void
    {
        $eleveurs = DB::table('eleveurs')->pluck('id_utilisateur')->values();
        $servicesParVeterinaire = DB::table('services_veterinaires')->get()->groupBy('id_veterinaire');
        $veterinaires = $servicesParVeterinaire->keys()->values();

        $statuts = ['confirme', 'realise', 'en_attente', 'realise'];

        foreach (range(0, 3) as $i) {
            $idVeterinaire = $veterinaires[$i % $veterinaires->count()];
            $service = $servicesParVeterinaire[$idVeterinaire]->first();

            DB::table('rendez_vous')->insert([
                'id_eleveur' => $eleveurs[$i % $eleveurs->count()],
                'id_veterinaire' => $idVeterinaire,
                'id_service' => $service->id_service,
                'sujet' => 'Suivi sanitaire du troupeau',
                'description' => 'Demande de consultation pour suivi sanitaire.',
                'date_prevue' => now()->addDays($i + 1)->setTime(9, 0),
                'statut' => $statuts[$i],
                'date_creation' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
