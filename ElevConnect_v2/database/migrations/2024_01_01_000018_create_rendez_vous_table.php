<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rendez-vous de consultation vétérinaire.
 * Paiement effectué hors plateforme, entre éleveur et vétérinaire.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->increments('id_rdv');
            $table->unsignedInteger('id_eleveur');
            $table->unsignedInteger('id_veterinaire');
            $table->unsignedInteger('id_service')->nullable();
            $table->string('sujet', 255)->nullable();
            $table->text('description')->nullable();
            $table->dateTime('date_prevue');
            $table->enum('statut', ['en_attente', 'confirme', 'realise', 'annule', 'refuse'])->default('en_attente');
            $table->string('image_1', 255)->nullable();
            $table->string('image_2', 255)->nullable();
            $table->string('video', 255)->nullable();
            $table->unsignedTinyInteger('note_client')->nullable();
            $table->text('avis_client')->nullable();
            $table->dateTime('date_creation')->useCurrent();
            $table->timestamps();

            $table->foreign('id_eleveur')->references('id_utilisateur')->on('eleveurs')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_veterinaire')->references('id_utilisateur')->on('veterinaires')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_service')->references('id_service')->on('services_veterinaires')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->index('statut', 'idx_rdv_statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendez_vous');
    }
};
