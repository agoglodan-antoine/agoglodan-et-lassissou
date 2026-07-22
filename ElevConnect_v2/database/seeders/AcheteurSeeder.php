<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcheteurSeeder extends Seeder
{
    public function run(): void
    {
        $ids = DB::table('utilisateurs')->where('role', 'acheteur')->pluck('id_utilisateur')->values();

        $types = ['particulier', 'particulier', 'professionnel', 'professionnel'];

        foreach ($ids as $i => $id) {
            DB::table('acheteurs')->insert([
                'id_utilisateur' => $id,
                'type_acheteur' => $types[$i] ?? 'particulier',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
