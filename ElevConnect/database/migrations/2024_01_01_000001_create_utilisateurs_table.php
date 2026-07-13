<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table UTILISATEURS — compte commun à tous les acteurs de la plateforme.
 * Traduction directe de ElevConnect_schema_bdd_v4.sql (§1), enrichie de
 * `latitude`/`longitude` : la position GPS captée à l'inscription (règle de
 * gestion transversale, chap. 3) est commune à tous les rôles, elle est donc
 * stockée une seule fois ici plutôt que répétée dans chacune des 7 tables
 * de profil (ELEVEURS, ACHETEURS, VENDEUR_PROVENDE, VENDEUR_ACCESSOIRE,
 * VETERINAIRES, LIVREURS, ADMINISTRATEURS).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->increments('id_utilisateur');
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->string('telephone', 20)->nullable();
            $table->string('adresse', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('role', [
                'eleveur', 'acheteur', 'vendeur_provende', 'vendeur_accessoire',
                'veterinaire', 'livreur', 'administrateur',
            ]);
            $table->enum('statut', ['actif', 'suspendu'])->default('actif');
            $table->dateTime('date_inscription')->useCurrent();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
