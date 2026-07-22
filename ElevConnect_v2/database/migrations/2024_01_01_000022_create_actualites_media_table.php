<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Pièces jointes (image, vidéo, document) rattachées à une actualité. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actualites_media', function (Blueprint $table) {
            $table->increments('id_media');
            $table->unsignedInteger('id_actualite');
            $table->string('chemin_fichier', 255);
            $table->enum('type_media', ['image', 'video', 'document'])->default('image');
            $table->timestamps();

            $table->foreign('id_actualite')->references('id_actualite')->on('actualites')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actualites_media');
    }
};
