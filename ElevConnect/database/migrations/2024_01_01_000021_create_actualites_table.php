<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Articles de valorisation — tous rôles sauf Acheteur (contrainte appliquée par policy + trigger). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actualites', function (Blueprint $table) {
            $table->increments('id_actualite');
            $table->unsignedInteger('id_auteur');
            $table->string('titre', 200);
            $table->text('contenu');
            $table->dateTime('date_publication')->useCurrent();
            $table->timestamps();

            $table->foreign('id_auteur')->references('id_utilisateur')->on('utilisateurs')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->index('id_auteur', 'idx_actualites_auteur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actualites');
    }
};
