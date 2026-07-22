<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EleveurSeeder extends Seeder
{
    public function run(): void
    {
        $ids = DB::table('utilisateurs')->where('role', 'eleveur')->pluck('id_utilisateur')->values();

        $exploitations = ['Ferme Zébu du Nord', 'Élevage Wassangari', 'Ranch Borgou', 'Ferme Avicole Alafia'];

        foreach ($ids as $i => $id) {
            DB::table('eleveurs')->insert([
                'id_utilisateur' => $id,
                'nom_exploitation' => $exploitations[$i] ?? 'Exploitation ' . ($i + 1),
                'certificat_chemin' => 'certificats/eleveur_' . $id . '.pdf',
                'note_moyenne' => round(rand(30, 50) / 10, 2),
                'nombre_avis' => rand(2, 20),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
