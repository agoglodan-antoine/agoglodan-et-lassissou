<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attributs spécifiques au rôle Livreur.
 * La localisation (latitude/longitude) vit sur UTILISATEURS (voir cette migration).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livreurs', function (Blueprint $table) {
            $table->unsignedInteger('id_utilisateur')->primary();
            $table->string('moyen_transport', 100)->nullable();
            $table->string('zone_couverture', 150)->nullable();
            $table->timestamps();

            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateurs')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livreurs');
    }
};
