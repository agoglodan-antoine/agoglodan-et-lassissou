<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LivreurSeeder extends Seeder
{
    public function run(): void
    {
        $ids = DB::table('utilisateurs')->where('role', 'livreur')->pluck('id_utilisateur')->values();

        $moyens = ['Moto', 'Tricycle', 'Camionnette', 'Moto'];
        $zones = ['Parakou et environs', 'Cotonou', 'Porto-Novo', 'Zou'];

        foreach ($ids as $i => $id) {
            DB::table('livreurs')->insert([
                'id_utilisateur' => $id,
                'moyen_transport' => $moyens[$i] ?? 'Moto',
                'zone_couverture' => $zones[$i] ?? 'Bénin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
