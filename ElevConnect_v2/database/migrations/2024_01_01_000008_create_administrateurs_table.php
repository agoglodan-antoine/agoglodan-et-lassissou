<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attributs spécifiques au rôle Administrateur.
 * La localisation (latitude/longitude) vit sur UTILISATEURS (voir cette migration).
 * Cette table reste très fine : elle existe surtout pour respecter le schéma
 * Merise d'origine (1 table de profil par rôle), sans attribut propre à ce jour.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrateurs', function (Blueprint $table) {
            $table->unsignedInteger('id_utilisateur')->primary();
            $table->timestamps();

            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateurs')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrateurs');
    }
};
