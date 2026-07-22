<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VeterinaireSeeder extends Seeder
{
    public function run(): void
    {
        $ids = DB::table('utilisateurs')->where('role', 'veterinaire')->pluck('id_utilisateur')->values();

        $specialites = ['Médecine bovine', 'Aviculture', 'Petits ruminants', 'Médecine porcine'];
        $zones = ['Borgou', 'Littoral', 'Zou', 'Atacora'];

        foreach ($ids as $i => $id) {
            DB::table('veterinaires')->insert([
                'id_utilisateur' => $id,
                'specialite' => $specialites[$i] ?? 'Généraliste',
                'zone_intervention' => $zones[$i] ?? 'Bénin',
                'certificat_chemin' => 'certificats/veterinaire_' . $id . '.pdf',
                'note_moyenne' => round(rand(35, 50) / 10, 2),
                'nombre_avis' => rand(2, 20),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
