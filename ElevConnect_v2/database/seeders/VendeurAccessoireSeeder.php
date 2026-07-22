<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendeurAccessoireSeeder extends Seeder
{
    public function run(): void
    {
        $ids = DB::table('utilisateurs')->where('role', 'vendeur_accessoire')->pluck('id_utilisateur')->values();

        $boutiques = ['Accessoires Élevage Bénin', 'Matériel Agro Plus', 'Ferme Équipements', 'Zootechnie Shop'];

        foreach ($ids as $i => $id) {
            DB::table('vendeur_accessoire')->insert([
                'id_utilisateur' => $id,
                'nom_boutique' => $boutiques[$i] ?? 'Boutique ' . ($i + 1),
                'certificat_chemin' => 'certificats/vendeur_accessoire_' . $id . '.pdf',
                'note_moyenne' => round(rand(30, 50) / 10, 2),
                'nombre_avis' => rand(2, 20),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
