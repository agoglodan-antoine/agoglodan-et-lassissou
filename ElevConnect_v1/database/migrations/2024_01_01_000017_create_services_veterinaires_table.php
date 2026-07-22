<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Services et tarifs proposés par les vétérinaires. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services_veterinaires', function (Blueprint $table) {
            $table->increments('id_service');
            $table->unsignedInteger('id_veterinaire');
            $table->string('titre_service', 150);
            $table->text('description')->nullable();
            $table->decimal('prix', 12, 2);
            $table->unsignedSmallInteger('temps_traitement')->comment('Durée estimée en minutes');
            $table->string('photo_illustrative', 255)->nullable();
            $table->enum('statut_service', ['disponible', 'indisponible'])->default('disponible');
            $table->timestamps();

            $table->foreign('id_veterinaire')->references('id_utilisateur')->on('veterinaires')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services_veterinaires');
    }
};
