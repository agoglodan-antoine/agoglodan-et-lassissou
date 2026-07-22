<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanningLivreurSeeder extends Seeder
{
    public function run(): void
    {
        $livreurs = DB::table('livreurs')->pluck('id_utilisateur')->values();

        foreach ($livreurs as $i => $id) {
            DB::table('planning_livreur')->insert([
                'id_livreur' => $id,
                'date_debut' => now()->addDays($i)->setTime(8, 0),
                'date_fin' => now()->addDays($i)->setTime(18, 0),
                'disponible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
