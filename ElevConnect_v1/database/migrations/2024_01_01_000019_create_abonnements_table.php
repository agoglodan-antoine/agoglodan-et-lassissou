<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Historique des abonnements souscrits par les vétérinaires (seul rôle éligible). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abonnements', function (Blueprint $table) {
            $table->increments('id_abonnement');
            $table->unsignedInteger('id_veterinaire');
            $table->enum('formule', ['basique', 'premium']);
            $table->date('date_debut');
            $table->date('date_expiration');
            $table->enum('statut', ['actif', 'expire'])->default('actif');
            $table->timestamps();

            $table->foreign('id_veterinaire')->references('id_utilisateur')->on('veterinaires')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->index('statut', 'idx_abonnements_statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};
