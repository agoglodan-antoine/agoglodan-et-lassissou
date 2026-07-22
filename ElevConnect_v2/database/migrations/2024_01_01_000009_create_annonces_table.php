<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Catalogue unifié : annonces animal / provende / accessoire. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annonces', function (Blueprint $table) {
            $table->increments('id_annonce');
            $table->unsignedInteger('id_utilisateur');
            $table->enum('type_annonce', ['animal', 'provende', 'accessoire']);
            $table->string('titre', 150);
            $table->text('description')->nullable();
            $table->decimal('prix_unitaire', 12, 2);
            $table->unsignedInteger('quantite')->default(1);
            $table->decimal('poids', 8, 2)->nullable()->comment("Poids en kg (type animal uniquement)");
            $table->unsignedSmallInteger('mois')->nullable()->comment("Âge en mois (type animal uniquement)");
            $table->enum('unite_de_mesure', ['sac', 'kg', 'l', 'bassine', 'autre'])->nullable();
            $table->string('image_1', 255);
            $table->string('image_2', 255)->nullable();
            $table->enum('statut', ['en_attente', 'visible', 'rejetee'])->default('en_attente');
            $table->string('motif_rejet', 255)->nullable();
            $table->enum('etat', ['disponible', 'stock_epuise'])->default('disponible');
            $table->dateTime('date_publication')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateurs')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->index('statut', 'idx_annonces_statut');
            $table->index('type_annonce', 'idx_annonces_type');
            $table->index('id_utilisateur', 'idx_annonces_utilisateur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};
