<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdministrateurSeeder extends Seeder
{
    public function run(): void
    {
        $ids = DB::table('utilisateurs')->where('role', 'administrateur')->pluck('id_utilisateur')->values();

        foreach ($ids as $id) {
            DB::table('administrateurs')->insert([
                'id_utilisateur' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
