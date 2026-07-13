<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attributs spécifiques au rôle Acheteur.
 * La localisation (latitude/longitude) vit sur UTILISATEURS (voir cette migration).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acheteurs', function (Blueprint $table) {
            $table->unsignedInteger('id_utilisateur')->primary();
            $table->enum('type_acheteur', ['particulier', 'professionnel'])->default('particulier');
            $table->timestamps();

            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateurs')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acheteurs');
    }
};
