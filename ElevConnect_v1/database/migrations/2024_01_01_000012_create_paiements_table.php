<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paiement en séquestre associé à une commande, jusqu'au reversement (VERSEMENTS).
 * Commission ElevConnect = 5% (cf. cahier des charges, chap. 2 et 3 — voir config('elevconnect.commission_rate')).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->increments('id_paiement');
            $table->unsignedInteger('id_commande')->unique();
            $table->decimal('montant_net_commande', 12, 2);
            $table->decimal('montant_net_livraison', 12, 2)->default(0);
            $table->decimal('total_general', 12, 2);
            $table->enum('moyen_de_paiement', ['mobile_money', 'carte_bancaire']);
            $table->string('numero_de_compte', 50);
            $table->decimal('commission_sur_commande', 12, 2);
            $table->decimal('commission_sur_livraison', 12, 2)->default(0);
            $table->decimal('total_commission', 12, 2);
            $table->decimal('montant_a_verser_au_fournisseur', 12, 2);
            $table->decimal('montant_a_verser_au_livreur', 12, 2)->default(0);
            $table->enum('statut_paiement', ['en_attente', 'reussi', 'echoue', 'rembourse'])->default('en_attente');
            $table->dateTime('date_paiement')->nullable();
            $table->timestamps();

            $table->foreign('id_commande')->references('id_commande')->on('commandes')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->index('statut_paiement', 'idx_paiements_statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
