<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Notifications système par utilisateur (in-app + email). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications_elevconnect', function (Blueprint $table) {
            // Nommée `notifications_elevconnect` pour ne pas entrer en conflit avec la
            // table `notifications` native de Laravel si le canal notifiable est activé plus tard.
            $table->increments('id_notification');
            $table->unsignedInteger('id_utilisateur');
            $table->string('contenu', 255);
            $table->string('type', 50)->nullable()
                ->comment('annonce_approuvee, annonce_rejetee, commande, livraison, abonnement...');
            $table->boolean('lu')->default(false);
            $table->dateTime('date_creation')->useCurrent();
            $table->timestamps();

            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateurs')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->index(['id_utilisateur', 'lu'], 'idx_notifications_utilisateur_lu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_elevconnect');
    }
};
