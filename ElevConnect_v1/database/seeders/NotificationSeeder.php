<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $utilisateurs = DB::table('utilisateurs')->inRandomOrder()->limit(4)->pluck('id_utilisateur')->values();

        $types = ['annonce_approuvee', 'commande', 'livraison', 'abonnement'];
        $contenus = [
            'Votre annonce a été approuvée et est désormais visible.',
            'Votre commande a été confirmée par le fournisseur.',
            "Votre livraison est en cours d'acheminement.",
            'Votre abonnement premium a été renouvelé.',
        ];

        foreach ($utilisateurs as $i => $id) {
            DB::table('notifications_elevconnect')->insert([
                'id_utilisateur' => $id,
                'contenu' => $contenus[$i],
                'type' => $types[$i],
                'lu' => (bool) ($i % 2),
                'date_creation' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
