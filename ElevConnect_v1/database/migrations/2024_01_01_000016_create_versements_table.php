<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reversements effectués par ElevConnect au fournisseur et, le cas échéant, au livreur. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versements', function (Blueprint $table) {
            $table->increments('id_versement');
            $table->unsignedInteger('id_commande');
            $table->unsignedInteger('id_paiement');
            $table->enum('type_beneficiaire', ['fournisseur', 'livreur']);
            $table->unsignedInteger('id_beneficiaire');
            $table->decimal('montant_verser', 12, 2);
            $table->enum('moyen_de_versement', ['mobile_money', 'carte_bancaire']);
            $table->string('numero_de_compte', 50);
            $table->enum('statut_versement', ['en_attente', 'reussi', 'echoue'])->default('en_attente');
            $table->dateTime('date_versement')->nullable();
            $table->timestamps();

            $table->foreign('id_commande')->references('id_commande')->on('commandes')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_paiement')->references('id_paiement')->on('paiements')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_beneficiaire')->references('id_utilisateur')->on('utilisateurs')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->index('statut_versement', 'idx_versements_statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versements');
    }
};
