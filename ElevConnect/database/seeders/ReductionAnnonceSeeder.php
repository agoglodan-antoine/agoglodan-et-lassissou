<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReductionAnnonceSeeder extends Seeder
{
    public function run(): void
    {
        $annonceIds = DB::table('annonces')->pluck('id_annonce')->values();

        $bareme = [
            ['quantite_min' => 5, 'quantite_max' => 9, 'pourcentage_reduction' => 3.00],
            ['quantite_min' => 10, 'quantite_max' => 19, 'pourcentage_reduction' => 5.00],
            ['quantite_min' => 20, 'quantite_max' => 49, 'pourcentage_reduction' => 8.00],
            ['quantite_min' => 50, 'quantite_max' => 100, 'pourcentage_reduction' => 12.00],
        ];

        foreach ($annonceIds as $i => $id) {
            $tranche = $bareme[$i % count($bareme)];
            DB::table('reductions_annonce')->insert([
                'id_annonce' => $id,
                'quantite_min' => $tranche['quantite_min'],
                'quantite_max' => $tranche['quantite_max'],
                'pourcentage_reduction' => $tranche['pourcentage_reduction'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
