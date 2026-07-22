<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Planning de disponibilité personnelle du livreur. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_livreur', function (Blueprint $table) {
            $table->increments('id_planning');
            $table->unsignedInteger('id_livreur');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->boolean('disponible')->default(true);
            $table->timestamps();

            $table->foreign('id_livreur')->references('id_utilisateur')->on('livreurs')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_livreur');
    }
};
