<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Commandes passées sur les annonces.
 * Cycle de vie : en_attente -> payee -> en_cours_de_traitement -> validee
 * -> en_cours_de_livraison -> livree -> confirmee
 * (annulee possible avant validee ; refusee/en_litige possibles entre livree et confirmee).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->increments('id_commande');
            $table->unsignedInteger('id_annonce');
            $table->unsignedInteger('id_acheteur');
            $table->unsignedInteger('quantite');
            $table->decimal('prix_unitaire', 12, 2);
            $table->decimal('montant_total', 12, 2);
            $table->decimal('reduction_sur_commande', 12, 2)->default(0);
            $table->decimal('montant_net_commande', 12, 2);
            $table->enum('statut', [
                'en_attente', 'payee', 'en_cours_de_traitement', 'annulee', 'validee',
                'en_cours_de_livraison', 'livree', 'confirmee', 'refusee', 'en_litige',
            ])->default('en_attente');
            $table->string('motif_de_rejet', 255)->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('note_client_commande')->nullable();
            $table->text('avis_client_commande')->nullable();
            $table->string('code_authenticite', 64)->unique()
                ->comment('Code encodé dans le QR code, scanné à la livraison');
            $table->dateTime('date_commande')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_annonce')->references('id_annonce')->on('annonces')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_acheteur')->references('id_utilisateur')->on('utilisateurs')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->index('statut', 'idx_commandes_statut');
            $table->index('id_acheteur', 'idx_commandes_acheteur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
