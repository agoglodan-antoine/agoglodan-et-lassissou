<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attributs spécifiques au rôle Éleveur (fournisseur : certificat + étoiles).
 * La localisation (latitude/longitude) est commune à tous les rôles et
 * vit désormais sur UTILISATEURS — voir la migration de cette table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eleveurs', function (Blueprint $table) {
            $table->unsignedInteger('id_utilisateur')->primary();
            $table->string('nom_exploitation', 150)->nullable();
            $table->string('certificat_chemin', 255)->nullable();
            $table->decimal('note_moyenne', 3, 2)->nullable();
            $table->unsignedInteger('nombre_avis')->default(0);
            $table->timestamps();

            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateurs')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eleveurs');
    }
};
