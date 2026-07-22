<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActualiteMediaSeeder extends Seeder
{
    public function run(): void
    {
        $actualites = DB::table('actualites')->pluck('id_actualite')->values();
        $types = ['image', 'image', 'video', 'document'];

        foreach ($actualites as $i => $id) {
            $type = $types[$i];
            $extension = $type === 'video' ? 'mp4' : ($type === 'document' ? 'pdf' : 'jpg');

            DB::table('actualites_media')->insert([
                'id_actualite' => $id,
                'chemin_fichier' => 'actualites/media_' . ($i + 1) . '.' . $extension,
                'type_media' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
