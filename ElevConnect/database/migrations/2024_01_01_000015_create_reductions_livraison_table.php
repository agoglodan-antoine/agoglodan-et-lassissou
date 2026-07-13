<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Barème de réduction sur les frais de transport, défini par le livreur. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reductions_livraison', function (Blueprint $table) {
            $table->increments('id_reduction');
            $table->unsignedInteger('id_livreur');
            $table->unsignedInteger('quantite_min');
            $table->unsignedInteger('quantite_max');
            $table->decimal('pourcentage_reduction', 5, 2);
            $table->timestamps();

            $table->foreign('id_livreur')->references('id_utilisateur')->on('livreurs')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reductions_livraison');
    }
};
