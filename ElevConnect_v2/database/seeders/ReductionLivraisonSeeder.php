<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReductionLivraisonSeeder extends Seeder
{
    public function run(): void
    {
        $livreurs = DB::table('livreurs')->pluck('id_utilisateur')->values();

        $bareme = [
            ['quantite_min' => 3, 'quantite_max' => 5, 'pourcentage_reduction' => 5.00],
            ['quantite_min' => 6, 'quantite_max' => 10, 'pourcentage_reduction' => 8.00],
            ['quantite_min' => 11, 'quantite_max' => 20, 'pourcentage_reduction' => 10.00],
            ['quantite_min' => 21, 'quantite_max' => 50, 'pourcentage_reduction' => 15.00],
        ];

        foreach ($livreurs as $i => $id) {
            $tranche = $bareme[$i % count($bareme)];
            DB::table('reductions_livraison')->insert([
                'id_livreur' => $id,
                'quantite_min' => $tranche['quantite_min'],
                'quantite_max' => $tranche['quantite_max'],
                'pourcentage_reduction' => $tranche['pourcentage_reduction'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
