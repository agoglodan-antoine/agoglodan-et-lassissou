<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Livraison associée à une commande — intervention du livreur optionnelle.
 * L'acheteur choisit un livreur précis à la commande (COMMANDES.id_livreur_souhaite) ;
 * ce livreur est celui initialement assigné ici (id_livreur). En cas de refus,
 * la livraison est reproposée automatiquement à un autre livreur disponible et
 * l'historique des refus est conservé dans livreurs_ayant_refuse, pour ne pas
 * reproposer deux fois au même livreur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livraison', function (Blueprint $table) {
            $table->increments('id_livraison');
            $table->unsignedInteger('id_commande')->unique();
            $table->unsignedInteger('id_livreur')->nullable();
            $table->string('adresse_fournisseur', 255);
            $table->string('adresse_client', 255);
            $table->decimal('frais_de_livraison', 12, 2)->default(0);
            $table->decimal('reduction_sur_frais', 12, 2)->default(0);
            $table->decimal('montant_net_livraison', 12, 2)->default(0);
            $table->enum('statut', ['en_attente', 'prise_en_charge', 'rejetee', 'en_cours', 'terminee'])
                ->default('en_attente');
            $table->enum('verification_authenticite', ['en_attente', 'verifiee', 'echoue'])->default('en_attente');
            $table->dateTime('date_verification_qr')->nullable()
                ->comment('Date et heure du scan du QR code par le client');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('note_client_livraison')->nullable();
            $table->text('avis_client_livraison')->nullable();
            $table->json('livreurs_ayant_refuse')->nullable();
            $table->timestamps();

            $table->foreign('id_commande')->references('id_commande')->on('commandes')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('id_livreur')->references('id_utilisateur')->on('livreurs')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->index('statut', 'idx_livraison_statut');
            $table->index('id_livreur', 'idx_livraison_livreur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livraison');
    }
};
