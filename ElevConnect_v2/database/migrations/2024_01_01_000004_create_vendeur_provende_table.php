<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attributs spécifiques au rôle Vendeur de provende.
 * La localisation (latitude/longitude) vit sur UTILISATEURS (voir cette migration).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendeur_provende', function (Blueprint $table) {
            $table->unsignedInteger('id_utilisateur')->primary();
            $table->string('nom_boutique', 150)->nullable();
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
        Schema::dropIfExists('vendeur_provende');
    }
};
