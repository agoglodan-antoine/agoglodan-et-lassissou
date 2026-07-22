<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbonnementSeeder extends Seeder
{
    public function run(): void
    {
        $veterinaires = DB::table('veterinaires')->pluck('id_utilisateur')->values();
        $formules = ['basique', 'premium', 'basique', 'premium'];

        foreach ($veterinaires as $i => $id) {
            DB::table('abonnements')->insert([
                'id_veterinaire' => $id,
                'formule' => $formules[$i % count($formules)],
                'date_debut' => now()->subMonths(1)->toDateString(),
                'date_expiration' => now()->addMonths(11)->toDateString(),
                'statut' => 'actif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
