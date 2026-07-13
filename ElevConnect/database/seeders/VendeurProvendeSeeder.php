<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendeurProvendeSeeder extends Seeder
{
    public function run(): void
    {
        $ids = DB::table('utilisateurs')->where('role', 'vendeur_provende')->pluck('id_utilisateur')->values();

        $boutiques = ['Provende Plus', 'Alimentation Bétail Sarl', 'Nutri-Ferme', 'Grenier du Borgou'];

        foreach ($ids as $i => $id) {
            DB::table('vendeur_provende')->insert([
                'id_utilisateur' => $id,
                'nom_boutique' => $boutiques[$i] ?? 'Boutique ' . ($i + 1),
                'certificat_chemin' => 'certificats/vendeur_provende_' . $id . '.pdf',
                'note_moyenne' => round(rand(30, 50) / 10, 2),
                'nombre_avis' => rand(2, 20),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
